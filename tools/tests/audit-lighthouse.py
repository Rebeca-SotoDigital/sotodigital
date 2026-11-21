#!/usr/bin/env python3
"""Run 3 mobile Lighthouse passes per route against both isolated snapshots."""
from pathlib import Path
import subprocess,os,json,time
root=Path(__file__).resolve().parents[2]
node=os.environ['SOTO_AUDIT_NODE'];cli=os.environ['SOTO_AUDIT_LIGHTHOUSE']
out=root/'docs/auditoria-2026-11';summary=[]
if os.environ.get('SOTO_AUDIT_STAGE') and (root/'docs/auditoria-2026-11/lighthouse-summary.json').exists():
 summary=[row for row in json.loads((root/'docs/auditoria-2026-11/lighthouse-summary.json').read_text()) if row['stage']!=os.environ['SOTO_AUDIT_STAGE']]
stages=[('before',8093),('after',8092)]
if os.environ.get('SOTO_AUDIT_STAGE'): stages=[item for item in stages if item[0]==os.environ['SOTO_AUDIT_STAGE']]
for stage,port in stages:
 for name,path in [('home','/'),('google-ads','/campanas/google-ads.php'),('servicio','/diseno-web.php')]:
  for run in range(1,4):
   report=out/f'lighthouse-{stage}-{name}-{run}.json'
   command=[node,cli,f'http://127.0.0.1:{port}{path}','--chrome-flags=--headless --no-sandbox --no-proxy-server','--only-categories=performance,accessibility','--output=json','--output-path='+str(report),'--quiet','--blocked-url-patterns=https://*']
   result=subprocess.run(command,capture_output=True,text=True,cwd=root,env={**os.environ,'CHROME_PATH':os.environ.get('SOTO_AUDIT_CHROME','/Applications/Google Chrome.app/Contents/MacOS/Google Chrome')},timeout=180)
   if result.returncode:raise RuntimeError(result.stderr[-2000:])
   data=json.loads(report.read_text());audits=data['audits']
   row={'stage':stage,'page':name,'run':run,'performance':round(data['categories']['performance']['score']*100),'accessibility':round(data['categories']['accessibility']['score']*100),'lcp_ms':round(audits['largest-contentful-paint']['numericValue']),'cls':audits['cumulative-layout-shift']['numericValue'],'tbt_ms':audits['total-blocking-time']['numericValue']}
   summary.append(row);(out/'lighthouse-summary.json').write_text(json.dumps(summary,indent=2));print(json.dumps(row),flush=True)
