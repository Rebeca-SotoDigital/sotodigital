<?php
$organizationId='https://soto-digital.com/#organizacion';
$schema=[
 '@context'=>'https://schema.org',
 '@graph'=>[
  ['@type'=>'Organization','@id'=>$organizationId,'name'=>'Soto Digital','legalName'=>'Soto Digital S.L.','url'=>'https://soto-digital.com/','logo'=>'https://soto-digital.com/img/optimized/logo-480.png','sameAs'=>['https://www.instagram.com/sotodigital/']],
  ['@type'=>'ProfessionalService','@id'=>'https://soto-digital.com/#negocio','name'=>'Soto Digital','url'=>'https://soto-digital.com/','parentOrganization'=>['@id'=>$organizationId],'description'=>'Diseño web, redes sociales, branding, contenidos y campañas para empresas y negocios.','areaServed'=>[['@type'=>'Country','name'=>'España'],['@type'=>'AdministrativeArea','name'=>'Cantabria']]],
  ['@type'=>'WebSite','@id'=>'https://soto-digital.com/#web','url'=>'https://soto-digital.com/','name'=>'Soto Digital','inLanguage'=>'es-ES','publisher'=>['@id'=>$organizationId]],
 ],
];
?>
<script type="application/ld+json"><?= json_encode($schema,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) ?></script>
