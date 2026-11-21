<?php
function campaign_create_deal(PDO $db, array $row, ?callable $http=null): void {
    if(!filter_var(campaign_config('BREVO_CREATE_DEALS',false),FILTER_VALIDATE_BOOLEAN) || $row['deal_state']!=='pending' || !$row['brevo_synced'])return;
    $data=json_decode($row['payload'],true);
    if(campaign_stored_score($data)['category']!=='caliente')return;
    $pipeline=campaign_config('BREVO_PIPELINE_ID');$stage=campaign_config('BREVO_NEW_STAGE_ID');
    if(!$pipeline||!$stage||!campaign_config('BREVO_API_KEY'))return;
    $http=$http??'campaign_http';$headers=['api-key: '.campaign_config('BREVO_API_KEY')];$contact=(int)$row['brevo_contact_id'];
    if(!$contact){$lookup=$http('https://api.brevo.com/v3/contacts/'.rawurlencode($data['email']),$headers,[],12,'GET');$contact=(int)($lookup['body']['id']??0);if(!$contact)return;}
    $db->prepare("UPDATE leads SET deal_state='sending' WHERE id=?")->execute([$row['id']]);
    $result=$http('https://api.brevo.com/v3/crm/deals',$headers,['name'=>'Solicitud '.$row['service'].' · '.substr($row['id'],0,8),'attributes'=>['pipeline'=>$pipeline,'deal_stage'=>$stage],'linkedContactsIds'=>[$contact]]);
    if($result['status']>=200&&$result['status']<300&&!empty($result['body']['id']))$db->prepare("UPDATE leads SET deal_state='complete',brevo_deal_id=? WHERE id=?")->execute([(string)$result['body']['id'],$row['id']]);
    elseif($result['status']>=400&&$result['status']<500)$db->prepare("UPDATE leads SET deal_state='pending' WHERE id=?")->execute([$row['id']]);
}
function campaign_weekly_report(PDO $db, ?int $now=null): array {
    $date=new DateTimeImmutable('@'.($now??time()));$start=$date->modify('monday this week')->setTime(0,0);$end=$start->modify('+7 days');
    $stmt=$db->prepare('SELECT service,payload,attribution,admin_sent,confirmation_sent,brevo_synced FROM leads WHERE created_at>=? AND created_at<?');$stmt->execute([$start->format('c'),$end->format('c')]);
    $groups=[];
    foreach($stmt as $row){$data=json_decode($row['payload'],true);$attr=json_decode($row['attribution'],true);$campaign=(string)($attr['utm_campaign']??'');
        $campaign=$campaign===''?'sin-campana':(preg_match('/^[0-9]{1,30}$/',$campaign)?$campaign:'camp_'.substr(hash('sha256',$campaign),0,12));
        $category=campaign_stored_score($data)['category'];$state=$row['admin_sent']&&$row['confirmation_sent']&&$row['brevo_synced']?'procesado':'pendiente';
        $key=$row['service'].'|'.$campaign.'|'.$category.'|'.$state;
        if(!isset($groups[$key]))$groups[$key]=['service'=>$row['service'],'campaign'=>$campaign,'category'=>$category,'state'=>$state,'leads'=>0];$groups[$key]['leads']++;
    }
    return ['from'=>$start->format('Y-m-d'),'to_exclusive'=>$end->format('Y-m-d'),'groups'=>array_values($groups)];
}
