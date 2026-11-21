<?php
if (PHP_SAPI !== 'cli') exit;
require __DIR__ . '/../../includes/campaign-leads.php';
function audit_check($condition, string $message): void { if (!$condition) throw new RuntimeException($message); }
$s = ['REMOTE_ADDR'=>'192.0.2.8','HTTP_CF_CONNECTING_IP'=>'198.51.100.9','HTTP_X_FORWARDED_FOR'=>'203.0.113.9'];
audit_check(campaign_client_ip($s,[])==='192.0.2.8','Untrusted IP spoofing');
audit_check(campaign_client_ip($s,['192.0.2.0/24'])==='198.51.100.9','Trusted CF proxy');
unset($s['HTTP_CF_CONNECTING_IP']);$s['HTTP_X_FORWARDED_FOR']='invalid, 2001:db8::1, 203.0.113.1';
audit_check(campaign_client_ip($s,['192.0.2.0/24'])==='2001:db8::1','Forwarded IPv6');
audit_check(campaign_ip_in_cidr('2001:db8::2','2001:db8::/32'),'IPv6 CIDR');
audit_check(!campaign_ip_in_cidr('2001:db9::2','2001:db8::/32'),'IPv6 CIDR outside');
audit_check(!campaign_ip_in_cidr('192.0.2.1','192.0.2.0/999'),'Bad CIDR');
echo "PASS: trusted proxies, spoof protection, CIDR IPv4/IPv6\n";
