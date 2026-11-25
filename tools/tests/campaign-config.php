<?php
// Isolated hosting-layout checks. No real keys, network or live configuration.
$base=sys_get_temp_dir().'/soto-config-test-'.bin2hex(random_bytes(5));
mkdir($base.'/html/includes',0700,true);mkdir($base.'/data/soto-private',0700,true);
copy(__DIR__.'/../../includes/campaign-config.php',$base.'/html/includes/campaign-config.php');
$fixture="<?php return ['FIXTURE'=>'private'];";
file_put_contents($base.'/data/soto-private/soto-config.php',$fixture);
file_put_contents($base.'/explicit.php',"<?php return ['FIXTURE'=>'explicit'];");
file_put_contents($base.'/html/public.php',$fixture);
$run=static function($setup,$expected)use($base){
    $code='foreach (["SOTO_CONFIG_FILE","REDIRECT_SOTO_CONFIG_FILE","REDIRECT_REDIRECT_SOTO_CONFIG_FILE","FIXTURE"] as $k) {putenv($k);unset($_SERVER[$k]);}'.
        '$_SERVER["DOCUMENT_ROOT"]='.var_export($base.'/html',true).';'.$setup.
        'require '.var_export($base.'/html/includes/campaign-config.php',true).';try {echo campaign_config("FIXTURE","missing");}catch(Throwable $e){echo $e->getMessage();}';
    $p=proc_open([PHP_BINARY,'-r',$code],[1=>['pipe','w'],2=>['pipe','w']],$pipes);
    $out=stream_get_contents($pipes[1]);$err=stream_get_contents($pipes[2]);fclose($pipes[1]);fclose($pipes[2]);$exit=proc_close($p);
    if($exit!==0||$err!==''||$out!==$expected)throw new RuntimeException('Configuration fixture failed: '.$expected);
};
try {
    $explicit=var_export($base.'/explicit.php',true);
    $run('', 'private');
    $run('putenv("SOTO_CONFIG_FILE=".'.$explicit.');', 'explicit');
    foreach(['SOTO_CONFIG_FILE','REDIRECT_SOTO_CONFIG_FILE','REDIRECT_REDIRECT_SOTO_CONFIG_FILE'] as $key){
        $run('$_SERVER['.var_export($key,true).']='.$explicit.';', 'explicit');
    }
    $run('putenv("REDIRECT_SOTO_CONFIG_FILE=".'.$explicit.');', 'explicit');
    $run('$_SERVER["HTTP_SOTO_CONFIG_FILE"]='.$explicit.';', 'private');
    $run('putenv("FIXTURE=environment");', 'environment');
    $run('putenv("SOTO_CONFIG_FILE=".'.var_export($base.'/html/public.php',true).');', 'Configuration must be outside public directories');
    $run('putenv("SOTO_CONFIG_FILE=".'.var_export($base.'/missing.php',true).');', 'Private configuration unavailable');
    unlink($base.'/data/soto-private/soto-config.php');
    $run('', 'missing');
    echo "PASS: private Arsys fallback, Apache/FastCGI/redirect variables, environment precedence, request-header rejection and public-config blocking\n";
} finally {
    $it=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($base,FilesystemIterator::SKIP_DOTS),RecursiveIteratorIterator::CHILD_FIRST);
    foreach($it as $file){if($file->isDir())rmdir($file->getPathname());else unlink($file->getPathname());}rmdir($base);
}
