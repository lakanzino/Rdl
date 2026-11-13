/**
 * محتوای اولیه‌ی سایت را به شکل بلوک‌های معتبر گوتنبرگ می‌سازد و در dlc-child/content می‌نویسد.
 * اجرا:  npm i @wordpress/blocks @wordpress/block-library jsdom global-jsdom  &&  node tools/build-content.cjs
 */
require('global-jsdom/register');
const noop = class { observe() {} unobserve() {} disconnect() {} };
global.MutationObserver = window.MutationObserver;
global.ResizeObserver = noop; global.IntersectionObserver = noop;
global.matchMedia = window.matchMedia = () => ({ matches: false, addListener() {}, removeListener() {}, addEventListener() {}, removeEventListener() {} });
const origErr = console.error; console.error = (...a) => { if (!String(a[0]).startsWith('memo:')) origErr(...a); };

const fs = require('fs'); const path = require('path');
const { registerCoreBlocks } = require('@wordpress/block-library');
const { createBlock: B, serialize, parse } = require('@wordpress/blocks');
registerCoreBlocks();

const OUT = path.join(__dirname, '..', 'dlc-child', 'content');
const pad = (v, h = '1.25rem') => ({ spacing: { padding: { top: v, bottom: v, left: h, right: h } } });

const h = (level, content, extra = {}) => B('core/heading', { level, content, ...extra });
const p = (content, extra = {}) => B('core/paragraph', { content, ...extra });
const ul = (items, ordered = false) => B('core/list', { ordered }, items.map((content) => B('core/list-item', { content })));
const btn = (text, url, className) => B('core/button', { text, url, ...(className ? { className } : {}) });
const btns = (list, className = 'dlc-buttons') => B('core/buttons', { className }, list);
const group = (attrs, inner) => B('core/group', { layout: { type: 'constrained' }, ...attrs }, inner);
const section = (attrs, inner) => group({ align: 'full', style: pad('4rem'), ...attrs }, inner);
const cols = (list, className) => B('core/columns', className ? { className } : {}, list.map((inner) => B('core/column', { className: 'dlc-card' }, inner)));
const en = (t) => `<span lang="en" dir="ltr">${t}</span>`;
const table = (head, rows) => B('core/table', {
  className: 'dlc-table', hasFixedLayout: false,
  head: [{ cells: head.map((content) => ({ content, tag: 'th' })) }],
  body: rows.map((r) => ({ cells: r.map((content) => ({ content, tag: 'td' })) })),
});

const docs = {};

/* ───────────── صفحه نخست ───────────── */
docs['front'] = [
  section({ className: 'dlc-hero dlc-center', backgroundColor: 'ast-global-color-6', textColor: 'ast-global-color-4', style: pad('5.5rem') }, [
    p('DLC Language Center', { className: 'dlc-eyebrow' }),
    h(1, 'آموزش زبان انگلیسی در دورود', { textColor: 'ast-global-color-4' }),
    p('از اولین کلمه تا مکالمه‌ی روان؛ زیر نظر استاد مسلط به زبان انگلیسی.', { className: 'dlc-lead' }),
    btns([
      btn('مشاهده‌ی دوره‌ها', '{{url:courses}}', 'is-style-dlc-accent'),
      btn('ثبت‌نام و مشاوره', '{{url:contact}}', 'is-style-outline'),
    ]),
  ]),

  section({ className: 'dlc-center' }, [
    h(2, 'چرا DLC؟'),
    p('یادگیری زبان وقتی نتیجه می‌دهد که مسیر روشن باشد و کسی کنار شما باشد که راه را بلد است.', { className: 'dlc-lead' }),
    cols([
      [h(3, 'استاد مسلط'), p('کلاس‌ها زیر نظر استاد مسلط به زبان انگلیسی برگزار می‌شود.')],
      [h(3, 'مسیر سطح‌بندی‌شده'), p('از سطح پایه شروع می‌کنید و قدم‌به‌قدم تا سطح‌های بالاتر پیش می‌روید.')],
      [h(3, 'تمرکز بر مکالمه'), p('هدف این است که بتوانید انگلیسی را بفهمید و صحبت کنید، نه فقط قواعد را حفظ کنید.')],
    ]),
  ]),

  section({ className: 'dlc-center', backgroundColor: 'ast-global-color-5' }, [
    h(2, 'مسیر یادگیری'),
    p('هر زبان‌آموز از سطح مناسب خودش شروع می‌کند.', { className: 'dlc-lead' }),
    cols([
      [p('A1', { className: 'dlc-level' }), h(3, 'Starter'), p('آشنایی با الفبا، واژه‌های پایه و جمله‌های ساده‌ی روزمره.')],
      [p('A2', { className: 'dlc-level' }), h(3, 'Elementary'), p('گفت‌وگوهای کوتاه درباره‌ی خود، خانواده، خرید و کارهای روزانه.')],
      [p('B1', { className: 'dlc-level' }), h(3, 'Intermediate'), p('بیان نظر، تعریف کردن ماجرا و فهم متن‌ها و گفت‌وگوهای معمول.')],
      [p('B2', { className: 'dlc-level' }), h(3, 'Upper-Intermediate'), p('مکالمه‌ی روان‌تر، درک متن‌های طولانی‌تر و نوشتن منسجم.')],
    ], 'dlc-levels'),
    btns([btn('جزئیات برنامه‌ی آموزشی', '{{url:courses}}')]),
  ]),

  section({}, [
    h(2, 'تازه‌ترین مقالات', { className: 'dlc-center-text' }),
    B('core/latest-posts', { postsToShow: 3, postLayout: 'grid', columns: 3, displayPostContent: true, excerptLength: 22, displayPostDate: true, className: 'dlc-posts' }),
  ]),

  section({ className: 'dlc-cta dlc-center', backgroundColor: 'ast-global-color-0', textColor: 'ast-global-color-4' }, [
    h(2, 'برای تعیین سطح و ثبت‌نام با ما در تماس باشید', { textColor: 'ast-global-color-4' }),
    btns([btn('تماس با آموزشگاه', '{{url:contact}}', 'is-style-dlc-accent')]),
  ]),
];

/* ───────────── دوره‌ها و برنامه‌ی آموزشی ───────────── */
docs['courses'] = [
  p('برنامه‌ی آموزشی آموزشگاه بر پایه‌ی سطح‌بندی بین‌المللی ' + en('CEFR') + ' چیده شده است. هر زبان‌آموز پس از تعیین سطح، از پله‌ی مناسب خودش شروع می‌کند.', { className: 'dlc-lead' }),
  h(2, 'سطح‌ها'),
  table(['سطح', en('CEFR'), 'در پایان این سطح'], [
    [en('Starter'), en('A1'), 'خودتان را معرفی می‌کنید و جمله‌ها و پرسش‌های ساده‌ی روزمره را می‌فهمید و می‌گویید.'],
    [en('Elementary'), en('A2'), 'درباره‌ی خانواده، کار، خرید و برنامه‌ی روزانه گفت‌وگوی کوتاه می‌کنید.'],
    [en('Intermediate'), en('B1'), 'نظر و تجربه‌ی خود را بیان می‌کنید و در موقعیت‌های معمول سفر و کار از پس گفت‌وگو برمی‌آیید.'],
    [en('Upper-Intermediate'), en('B2'), 'با روانی بیشتری صحبت می‌کنید، متن‌های طولانی‌تر را می‌فهمید و متن منسجم می‌نویسید.'],
  ]),
  h(2, 'چهار مهارت در هر سطح'),
  ul([
    '<strong>شنیدن (' + en('Listening') + '):</strong> فهم گفت‌وگوها و فایل‌های صوتی متناسب با سطح.',
    '<strong>صحبت کردن (' + en('Speaking') + '):</strong> تمرین مکالمه در هر جلسه.',
    '<strong>خواندن (' + en('Reading') + '):</strong> متن‌های کوتاه تا بلند همراه با واژگان تازه.',
    '<strong>نوشتن (' + en('Writing') + '):</strong> از جمله‌نویسی تا پاراگراف و متن کامل.',
  ]),
  h(2, 'ساختار هر جلسه'),
  ul([
    'مرور درس قبل و رفع اشکال',
    'واژگان و ساختار تازه',
    'تمرین شنیداری و مکالمه',
    'جمع‌بندی و تمرین برای خانه',
  ], true),
  h(2, 'زمان‌بندی و شهریه'),
  p('زمان‌بندی کلاس‌ها و شهریه‌ی هر دوره به‌زودی در همین صفحه اعلام می‌شود.'),
  btns([btn('تعیین سطح و ثبت‌نام', '{{url:contact}}', 'is-style-dlc-accent')], 'dlc-buttons dlc-start'),
];

/* ───────────── درباره ما ───────────── */
docs['about'] = [
  p(en('DLC Language Center') + ' آموزشگاه زبان انگلیسی در شهرستان دورود استان لرستان است.', { className: 'dlc-lead' }),
  p('در این آموزشگاه، علاقه‌مندان به یادگیری زبان انگلیسی زیر نظر استاد مسلط به زبان انگلیسی آموزش می‌بینند.'),
  h(2, 'دست‌اندرکاران'),
  ul([
    'مدیریت سایت و نویسنده‌ی مقالات: ' + en('Younes Delfan'),
    'طراح و توسعه‌دهنده‌ی وب‌سایت: ' + en('Mohamadreza Lak'),
  ]),
];

/* ───────────── تماس با ما ───────────── */
docs['contact'] = [
  p('برای تعیین سطح، ثبت‌نام یا هر پرسشی درباره‌ی کلاس‌ها با ما در تماس باشید.', { className: 'dlc-lead' }),
  ul([
    '<strong>نشانی:</strong> لرستان، دورود، [نشانی دقیق آموزشگاه]',
    '<strong>تلفن:</strong> [شماره‌ی تماس]',
    '<strong>ساعت پاسخ‌گویی:</strong> [روزها و ساعت‌ها]',
  ]),
];

/* ───────────── مقالات ───────────── */
docs['post-start'] = [
  p('بیشتر کسانی که می‌خواهند انگلیسی یاد بگیرند، با یک پرسش شروع می‌کنند: «از کجا شروع کنم؟» پاسخ ساده است: از جایی که هستید، با قدم‌های کوچک و منظم.'),
  h(2, '۱. سطح خودتان را بشناسید'),
  p('پیش از هر چیز بدانید کجای مسیر ایستاده‌اید. تعیین سطح کمک می‌کند نه وقتتان با مطالب تکراری هدر برود و نه با مطالب خیلی سخت دلسرد شوید.'),
  h(2, '۲. هدف روشن بگذارید'),
  p('«می‌خواهم انگلیسی یاد بگیرم» هدف نیست؛ «می‌خواهم تا شش ماه دیگر بتوانم خودم را معرفی کنم و یک گفت‌وگوی ساده داشته باشم» هدف است. هدف روشن، برنامه‌ی روشن می‌سازد.'),
  h(2, '۳. هر روز کمی، نه هفته‌ای یک‌بار زیاد'),
  p('بیست دقیقه تمرین روزانه از سه ساعت تمرینِ آخر هفته اثر بیشتری دارد. زبان با تکرارِ پیوسته در ذهن می‌نشیند.'),
  h(2, '۴. از روز اول صحبت کنید'),
  p('منتظر نمانید تا «آماده» شوید. از همان جمله‌های اول، بلند بگویید: ' + en('My name is … / I am from Dorud.') + ' اشتباه کردن بخشی از یادگیری است.'),
  h(2, '۵. همراه داشته باشید'),
  p('کلاس و معلم، مسیر را کوتاه می‌کنند: اشتباه‌ها زودتر اصلاح می‌شوند و نظم کلاس نمی‌گذارد وسط راه رها کنید.'),
];

docs['post-speaking'] = [
  p('مکالمه با تمرین روان می‌شود، نه با خواندنِ بیشتر درباره‌ی مکالمه. این پنج عادت ساده را می‌توانید از همین امروز شروع کنید.'),
  h(2, '۱. با خودتان انگلیسی حرف بزنید'),
  p('کارهایی را که انجام می‌دهید به انگلیسی بگویید: ' + en('I am making tea. I am going to class.') + ' کسی نمی‌شنود و ترسی هم در کار نیست.'),
  h(2, '۲. هر روز چند دقیقه گوش بدهید'),
  p('یک فایل صوتی یا ویدیوی کوتاهِ متناسب با سطحتان انتخاب کنید و هر روز گوش بدهید. گوش که به آهنگ زبان عادت کند، زبان هم راه می‌افتد.'),
  h(2, '۳. تکرار با صدای بلند'),
  p('یک جمله را بشنوید، مکث کنید و عیناً تکرار کنید. این تمرین تلفظ و سرعت شما را هم‌زمان بهتر می‌کند.'),
  h(2, '۴. عبارت یاد بگیرید، نه کلمه‌ی تنها'),
  p('به‌جای حفظ کردن ' + en('“interested”') + '، عبارت ' + en('“I’m interested in …”') + ' را یاد بگیرید. عبارت‌های آماده، هنگام صحبت زودتر به ذهن می‌آیند.'),
  h(2, '۵. صدای خودتان را ضبط کنید'),
  p('هفته‌ای یک‌بار یک دقیقه درباره‌ی روزتان صحبت کنید و ضبط کنید. بعد از یک ماه، ضبط اول را دوباره بشنوید تا پیشرفتتان را ببینید.'),
];

docs['post-vocabulary'] = [
  p('همه این تجربه را دارند: امروز ده لغت تازه یاد می‌گیریم و هفته‌ی بعد بیشترشان از یاد رفته است. مشکل از حافظه نیست؛ از روش است.'),
  h(2, 'لغت را در جمله یاد بگیرید'),
  p('کلمه‌ی تنها زود فراموش می‌شود. برای هر لغت یک جمله درباره‌ی زندگی خودتان بسازید: ' + en('I usually get up at seven.')),
  h(2, 'مرور با فاصله'),
  p('لغت تازه را همان روز، فردا، سه روز بعد، یک هفته بعد و یک ماه بعد مرور کنید. هر بار که درست پیش از فراموشی مرور می‌کنید، ماندگاری‌اش بیشتر می‌شود.'),
  h(2, 'کم ولی پیوسته'),
  p('پنج لغت در روز که واقعاً یاد گرفته شود، از سی لغتی که فقط از رویشان خوانده‌اید بهتر است.'),
  h(2, 'از لغت استفاده کنید'),
  ul([
    'در کلاس و هنگام صحبت، عمداً از لغت‌های تازه استفاده کنید.',
    'با لغت‌های هر هفته یک متن کوتاه بنویسید.',
    'لغت‌ها را دسته‌بندی کنید: خانه، کار، سفر، احساسات.',
  ]),
  h(2, 'دفترچه‌ی شخصی داشته باشید'),
  p('دفترچه یا فلش‌کارتِ خودتان را بسازید. نوشتن با دست، خودش یک‌بار مرور است.'),
];

docs['post-levels'] = [
  p('وقتی صحبت از سطح زبان می‌شود، حروفی مثل ' + en('A1') + ' یا ' + en('B2') + ' را زیاد می‌شنوید. این‌ها سطح‌های چارچوب اروپایی ' + en('CEFR') + ' هستند که توانایی زبانی را در شش پله توصیف می‌کند.'),
  h(2, 'سطح پایه'),
  ul([
    '<strong>' + en('A1') + ':</strong> عبارت‌های خیلی ساده و روزمره را می‌فهمید و خودتان را معرفی می‌کنید.',
    '<strong>' + en('A2') + ':</strong> درباره‌ی موضوع‌های آشنا مثل خانواده، خرید و کار، گفت‌وگوی کوتاه و ساده می‌کنید.',
  ]),
  h(2, 'سطح مستقل'),
  ul([
    '<strong>' + en('B1') + ':</strong> نکته‌های اصلی گفت‌وگوها و متن‌های معمول را می‌فهمید و درباره‌ی تجربه‌ها و برنامه‌هایتان صحبت می‌کنید.',
    '<strong>' + en('B2') + ':</strong> با روانی کافی با دیگران گفت‌وگو می‌کنید و متن‌های پیچیده‌تر را می‌فهمید.',
  ]),
  h(2, 'سطح پیشرفته'),
  ul([
    '<strong>' + en('C1') + ':</strong> زبان را در کار، تحصیل و زندگی اجتماعی روان و منعطف به کار می‌برید.',
    '<strong>' + en('C2') + ':</strong> تقریباً هر چه می‌شنوید و می‌خوانید را به‌راحتی می‌فهمید و دقیق و روان منظورتان را می‌رسانید.',
  ]),
  h(2, 'چرا دانستن سطح مهم است؟'),
  p('وقتی سطحتان را بدانید، کلاس و منبع مناسب انتخاب می‌کنید و پیشرفتتان قابل اندازه‌گیری می‌شود. قدم اول، تعیین سطح است.'),
];

let bad = 0;
for (const [name, blocks] of Object.entries(docs)) {
  const html = serialize(blocks);
  const check = (bs) => bs.forEach((b) => { if (!b.isValid) { bad++; console.log('INVALID', name, b.name); } check(b.innerBlocks); });
  check(parse(html));
  fs.writeFileSync(path.join(OUT, name + '.html'), html + '\n');
  console.log(name, html.length);
}
process.exit(bad ? 1 : 0);
