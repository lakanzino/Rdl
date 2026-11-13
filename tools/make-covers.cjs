/**
 * ساخت تصویر شاخص مقالات (۱۲۰۰×۶۳۰، فقط متن انگلیسی روی زمینه‌ی رنگی).
 * اجرا: NODE_PATH=<node_modules با puppeteer-core و @sparticuz/chromium> node tools/make-covers.cjs
 * خروجی: dlc-child/assets/img/cover-<key>.jpg — متن جایگزین (alt) فارسی در inc/setup.php است.
 */
const fs = require('fs');
const path = require('path');
const puppeteer = require('puppeteer-core');
const chromium = require('@sparticuz/chromium').default || require('@sparticuz/chromium');

const ROOT = path.join(__dirname, '..', 'dlc-child', 'assets');
const font = (f) => 'data:font/woff2;base64,' + fs.readFileSync(path.join(ROOT, 'fonts', f)).toString('base64');

// [کلید، برچسب، عنوان (‌| = شکستن خط)، زمینه، متن، رنگ تأکید، نقش]
const covers = [
  ['post-mig-start', 'IMMIGRATION · ROADMAP', 'English for|Immigration:|Where to Start', '#1D4E89', '#FFFFFF', '#FFC857', 'steps'],
  ['post-mig-british-american', 'ACCENTS · COMPARISON', 'British vs.|American|English', '#C8452F', '#FFFFFF', '#14243A', 'split'],
  ['post-mig-zero-to-ielts6', 'IELTS · TIMELINE', 'From Zero|to IELTS 6:|How Long?', '#0F766E', '#FFFFFF', '#FDE68A', 'bars'],
  ['post-speaking', 'SPEAKING · HABITS', '5 Daily Habits|for Better|Speaking', '#F2B632', '#14243A', '#C8452F', 'dots'],
  ['post-vocabulary', 'VOCABULARY · MEMORY', 'How to|Remember|New Words', '#5B3E96', '#FFFFFF', '#F9A8D4', 'cards'],
  ['post-levels', 'CEFR · LEVELS', 'English Levels|A1 to C2|Explained', '#14243A', '#FAF6EE', '#4FB3BF', 'levels'],
];

const art = (kind, fg, ac) => {
  const r = (x, y, w, h, c, o = 1, rx = 10) => `<rect x="${x}" y="${y}" width="${w}" height="${h}" rx="${rx}" fill="${c}" opacity="${o}"/>`;
  const t = (x, y, s, size, c) => `<text x="${x}" y="${y}" font-family="CP" font-weight="700" font-size="${size}" fill="${c}" text-anchor="middle">${s}</text>`;
  switch (kind) {
    case 'steps': return [0, 1, 2, 3].map((i) => r(40 + i * 80, 330 - i * 80, 80, 80 + i * 80, i === 3 ? ac : fg, i === 3 ? 1 : 0.25, 0)).join('') + `<circle cx="320" cy="50" r="26" fill="${ac}"/>`;
    case 'split': return r(40, 60, 150, 300, fg, 1, 18) + r(210, 60, 150, 300, ac, 1, 18) + t(115, 235, 'UK', 64, ac) + t(285, 235, 'US', 64, fg);
    case 'bars': return [90, 150, 210, 270, 340].map((hh, i) => r(40 + i * 68, 390 - hh, 48, hh, i === 4 ? ac : fg, i === 4 ? 1 : 0.3, 8)).join('') + t(336, 36, '6.0', 40, ac);
    case 'dots': return [0, 1, 2, 3, 4].map((i) => `<circle cx="${70 + (i % 3) * 120}" cy="${130 + Math.floor(i / 3) * 140}" r="48" fill="${i === 4 ? ac : fg}"/>` + t(70 + (i % 3) * 120, 148 + Math.floor(i / 3) * 140, String(i + 1), 52, i === 4 ? '#FFFFFF' : '#F2B632')).join('');
    case 'cards': return `<g transform="rotate(-8 200 210)">${r(70, 90, 240, 150, fg, 0.3, 16)}</g><g transform="rotate(5 200 210)">${r(80, 130, 240, 150, ac, 1, 16)}${t(200, 222, 'word', 52, '#3B2470')}</g>`;
    case 'levels': return ['A1', 'A2', 'B1', 'B2', 'C1', 'C2'].map((s, i) => r(40 + (i % 3) * 112, 90 + Math.floor(i / 3) * 132, 96, 112, i === 3 ? ac : fg, i === 3 ? 1 : 0.14, 14) + t(88 + (i % 3) * 112, 162 + Math.floor(i / 3) * 132, s, 40, i === 3 ? '#14243A' : fg)).join('');
  }
  return '';
};

const html = ([, kicker, title, bg, fg, ac, kind]) => `<!doctype html><html lang="en"><meta charset="utf-8"><style>
@font-face{font-family:CP;font-weight:400;src:url(${font('CourierPrime-Regular.woff2')})}
@font-face{font-family:CP;font-weight:700;src:url(${font('CourierPrime-Bold.woff2')})}
*{box-sizing:border-box;margin:0}
body{width:1200px;height:630px;background:${bg};color:${fg};font-family:CP,monospace;display:flex;align-items:center;padding:0 70px;overflow:hidden}
.t{flex:1}
.k{display:inline-block;font-size:22px;font-weight:700;letter-spacing:3px;padding:8px 16px;border:2px solid ${fg};border-radius:999px;margin-bottom:34px}
h1{font-size:80px;line-height:1.08;font-weight:700;letter-spacing:-1px}
h1 span{display:block}h1 span:nth-child(2){color:${ac}}
.b{margin-top:38px;font-size:24px;opacity:.85}
svg{width:400px;height:420px;flex:none}
</style><body><div class="t"><div class="k">${kicker}</div><h1>${title.split('|').map((l) => `<span>${l}</span>`).join('')}</h1><div class="b">dlclanguagecenter.ir</div></div>
<svg viewBox="0 0 400 420">${art(kind, fg, ac)}</svg></body></html>`;

(async () => {
  const browser = await puppeteer.launch({ executablePath: await chromium.executablePath(), args: [...chromium.args, '--no-sandbox'], headless: true });
  const page = await browser.newPage();
  await page.setViewport({ width: 1200, height: 630, deviceScaleFactor: 1 });
  for (const c of covers) {
    await page.setContent(html(c), { waitUntil: 'load' });
    await page.evaluate(() => document.fonts.ready);
    const out = path.join(ROOT, 'img', 'cover-' + c[0] + '.jpg');
    await page.screenshot({ path: out, type: 'jpeg', quality: 86 });
    console.log(out, fs.statSync(out).size);
  }
  await browser.close();
})();
