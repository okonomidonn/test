import os
D = os.path.dirname(os.path.abspath(__file__))

HEAD = '''<!DOCTYPE html><html lang="ja"><head><meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{title}</title>
<link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;500;700;900&family=Outfit:wght@500;600;700&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0&display=block" rel="stylesheet">
<link rel="stylesheet" href="style.css">
<style>{css}</style></head><body>'''

def header(on=''):
    def a(key, label, href):
        return f'<a href="{href}" class="{"on" if on == key else ""}">{label}</a>'
    return f'''<header class="hd"><div class="wrap">
<a class="logo" href="top.html">za<span>i</span>to</a>
<nav>{a("jobs","求人を探す","jobs.html")}{a("co","企業の方","#")}{a("about","zaitoについて","#")}</nav>
<div class="hd-r"><a class="btn btn-t hide-sp" href="#">ログイン</a><a class="btn btn-p" href="register.html">無料で登録</a>
<span class="burger"><span class="ms" style="font-size:22px">menu</span></span></div>
</div></header>'''

FOOT = '''<footer class="ft"><div class="wrap">
<div class="ft-top"><p>大学生・若手向け<br>完全在宅求人サービス</p>
<ul><li><a href="jobs.html">求人を探す</a></li><li><a href="#">企業の方</a></li><li><a href="#">zaitoについて</a></li>
<li><a href="#">よくある質問</a></li><li><a href="#">利用規約</a></li><li><a href="#">プライバシーポリシー</a></li></ul></div>
<div class="ft-b"><span>運営：zaito 運営事務局</span><small>Copyright © zaito</small></div>
<div class="logo ft-big" aria-hidden="true">za<span>i</span>to</div>
</div></footer></body></html>'''

JOBS = [
    dict(img='img/card-sns.webp', cat='SNS運用', title='SNS運用アシスタント（Instagram）', co='株式会社サンプルメディア', tags=['完全在宅', '未経験OK', '週10時間〜'], unit='時給', pay='1,500', new=True),
    dict(img='img/card-video.webp', cat='動画編集', title='ショート動画の編集（TikTok・リール）', co='合同会社サンプルスタジオ', tags=['完全在宅', '学生歓迎', '週2日〜'], unit='1本', pay='3,000', new=True),
    dict(img='img/card-web.webp', cat='Webマーケティング', title='Webマーケティングアシスタント', co='株式会社サンプルグロース', tags=['完全在宅', '学生歓迎', 'シフト自由'], unit='時給', pay='1,600', new=False),
    dict(ill=('ill-a', 'edit_note'), cat='ライティング', title='SEO記事の構成・執筆', co='株式会社サンプルコンテンツ', tags=['完全在宅', '未経験OK', '納期相談可'], unit='1文字', pay='1.5', new=True),
    dict(ill=('ill-b', 'support_agent'), cat='オンライン事務', title='オンライン事務・データ入力', co='株式会社サンプルワークス', tags=['完全在宅', '週10時間〜', '平日夜OK'], unit='時給', pay='1,200', new=False),
    dict(ill=('ill-d', 'auto_awesome'), cat='AI関連', title='生成AIの回答チェック・評価', co='株式会社サンプルAIラボ', tags=['完全在宅', '学生歓迎', '土日OK'], unit='時給', pay='1,400', new=False),
]

def jcard(j):
    ph = f'<img src="{j["img"]}" alt="">' if 'img' in j else f'<div class="ill {j["ill"][0]}"><span class="ms">{j["ill"][1]}</span></div>'
    new = '<span class="new">NEW</span>' if j['new'] else ''
    tags = ''.join(f'<span class="tag">{t}</span>' for t in j['tags'])
    return f'''<a class="jc" href="job.html"><div class="ph">{ph}<span class="fav"><span class="ms">favorite</span></span></div>
<div class="bd"><div style="display:flex;gap:6px;align-items:center"><span class="cat">{j["cat"]}</span>{new}</div>
<h3>{j["title"]}</h3><div class="co">{j["co"]}</div><div class="tags">{tags}</div>
<div class="pay">{j["unit"]}<b>{j["pay"]}</b>円〜</div></div></a>'''

def page(name, title, css, body):
    open(os.path.join(D, name), 'w').write(HEAD.format(title=title, css=css) + body + FOOT)

# ---------------- TOP ----------------
CATS = [('campaign', 'SNS運用', 42), ('movie', '動画編集', 31), ('trending_up', 'Webマーケ', 18), ('edit_note', 'ライティング', 27),
        ('support_agent', 'オンライン事務', 24), ('travel_explore', 'リサーチ', 12), ('keyboard', 'データ入力', 15), ('auto_awesome', 'AI関連', 9)]
top_css = '''
.hero{padding:72px 0 0}
.hero .wrap{display:grid;grid-template-columns:1.05fr .95fr;gap:64px;align-items:center}
.hero .badge{display:inline-flex;align-items:center;gap:8px;font-size:13px;font-weight:500;color:#4A5573}
.hero .badge i{width:7px;height:7px;border-radius:50%;background:#3D5AFE}
.hero h1{margin:24px 0 0;font-size:clamp(38px,5.4vw,68px);font-weight:900;line-height:1.22;letter-spacing:-.02em}
.hero .lead{margin:20px 0 0;font-size:18px;font-weight:700}
.hero .lead em{font-style:normal;color:#3D5AFE}
.search{margin-top:32px;display:flex;gap:8px;padding:8px;border-radius:16px;background:#fff;box-shadow:0 0 0 1.5px #D5DBE7,0 20px 40px -28px rgba(11,21,48,.35)}
.search label{flex:1;display:flex;align-items:center;gap:10px;padding:0 14px;min-width:0}
.search label+label{border-left:1px solid #EEF0F4}
.search .ms{color:#6B7590;font-size:22px}
.search input,.search select{border:0;outline:0;font:inherit;font-size:15px;width:100%;background:transparent;color:#0B1530;height:48px}
.popular{margin-top:16px;display:flex;flex-wrap:wrap;gap:8px;align-items:center;font-size:13px;color:#6B7590}
.popular a{display:inline-flex;height:30px;align-items:center;padding:0 12px;border-radius:999px;background:#F4F5F8;color:#3A4563;font-weight:500}
.stats{margin-top:36px;display:flex;gap:40px}
.stats div{display:flex;flex-direction:column}
.stats b{font-family:Outfit,sans-serif;font-size:32px;line-height:1.1}
.stats span{font-size:12px;color:#6B7590}
.collage{position:relative;height:600px}
.collage .jc{position:absolute;width:250px}
.collage .c1{left:0;top:80px;width:280px;z-index:2;box-shadow:0 30px 60px -30px rgba(11,21,48,.4)}
.collage .c2{right:0;top:0}
.collage .c3{right:0;top:318px}
.collage .c2 .tags,.collage .c3 .tags,.collage .c2 .co,.collage .c3 .co{display:none}
.collage .c2 .ph,.collage .c3 .ph{aspect-ratio:2/1}
.sec{padding-top:112px}
.grid3{display:grid;grid-template-columns:repeat(3,1fr);gap:24px}
.cats{display:grid;grid-template-columns:repeat(4,1fr);gap:16px}
.catc{display:flex;align-items:center;gap:16px;padding:20px;border-radius:16px;border:1px solid #E6E9F0;color:#0B1530}
.catc .ic{width:52px;height:52px;border-radius:14px;background:#EEF1FF;color:#3D5AFE;display:flex;align-items:center;justify-content:center}
.catc .ic .ms{font-size:28px}
.catc b{display:block;font-size:16px}
.catc span.n{font-size:12px;color:#6B7590}
.steps{display:grid;grid-template-columns:repeat(3,1fr);gap:24px;counter-reset:s}
.step{padding:32px;border-radius:20px;background:#F7F8FB}
.step .no{font-family:Outfit,sans-serif;font-size:14px;font-weight:700;color:#3D5AFE;letter-spacing:.1em}
.step h3{margin:12px 0 8px;font-size:20px}
.step p{margin:0;font-size:14px;color:#3A4563}
.coband{margin-top:112px;background:#0B1530;color:#fff;border-radius:28px;padding:56px;display:grid;grid-template-columns:1.3fr 1fr;gap:40px;align-items:center}
.coband h2{margin:8px 0 0;font-size:clamp(24px,3vw,34px);font-weight:900;line-height:1.4}
.coband p{margin:16px 0 0;color:#C9D0E4;font-size:15px}
.coband ul{list-style:none;margin:0;padding:0;display:flex;flex-direction:column;gap:14px}
.coband li{display:flex;gap:10px;align-items:center;font-weight:700}
.coband li .ms{color:#8FA2FF}
@media (max-width:860px){
 .hero{padding-top:36px}
 .hero .wrap{grid-template-columns:1fr;gap:40px}
 .search{flex-direction:column;padding:10px}
 .search label+label{border-left:0;border-top:1px solid #EEF0F4}
 .search .btn{width:100%}
 .collage{display:none}
 .stats{gap:28px}
 .grid3{grid-template-columns:1fr}
 .cats{grid-template-columns:1fr 1fr;gap:10px}
 .catc{flex-direction:column;align-items:flex-start;gap:10px;padding:16px}
 .steps{grid-template-columns:1fr}
 .coband{grid-template-columns:1fr;padding:32px 24px;border-radius:20px}
 .sec{padding-top:80px}
}
'''
cats = ''.join(f'<a class="catc" href="jobs.html"><span class="ic"><span class="ms">{i}</span></span><div><b>{n}</b><span class="n">{c}件</span></div></a>' for i, n, c in CATS)
top = header() + f'''
<section class="hero"><div class="wrap"><div>
<span class="badge"><i></i>大学生・若手向け 完全在宅求人サービス</span>
<h1>大学生のバイトを、<br>もっと自由に。</h1>
<p class="lead">授業のあいだに、自宅から。<em>完全在宅</em>の仕事だけを集めました。</p>
<form class="search" action="jobs.html">
<label><span class="ms">search</span><input placeholder="キーワード（例：Canva）"></label>
<label><span class="ms">work</span><select><option>職種を選ぶ</option></select></label>
<button class="btn btn-p btn-lg" type="button"><span class="ms" style="font-size:20px">search</span>検索</button></form>
<div class="popular">人気：<a href="#">未経験OK</a><a href="#">週10時間以下</a><a href="#">シフト自由</a><a href="#">土日のみOK</a></div>
<div class="stats"><div><b>128</b><span>掲載中の在宅求人</span></div><div><b>34</b><span>掲載企業</span></div><div><b>100<small style="font-size:16px">%</small></b><span>完全在宅</span></div></div>
</div>
<div class="collage">{jcard(JOBS[0]).replace('class="jc"','class="jc c1"',1)}{jcard(JOBS[1]).replace('class="jc"','class="jc c2"',1)}{jcard(JOBS[2]).replace('class="jc"','class="jc c3"',1)}</div>
</div></section>

<section class="sec"><div class="wrap">
<div class="sec-h"><div><div class="eyebrow">NEW JOBS</div><h2>新着の在宅求人</h2></div><a class="more" href="jobs.html">すべて見る<span class="ms" style="font-size:18px">arrow_forward</span></a></div>
<div class="grid3">{''.join(jcard(j) for j in JOBS)}</div></div></section>

<section class="sec"><div class="wrap">
<div class="sec-h"><div><div class="eyebrow">CATEGORY</div><h2>職種から探す</h2></div></div>
<div class="cats">{cats}</div></div></section>

<section class="sec"><div class="wrap">
<div class="sec-h"><div><div class="eyebrow">HOW IT WORKS</div><h2>はじめての在宅ワークも、3ステップ</h2></div></div>
<div class="steps">
<div class="step"><div class="no">STEP 01</div><h3>無料で登録</h3><p>メールアドレスかGoogleで1分。大学名や空いている時間を入れておくと、合う求人が見つかりやすくなります。</p></div>
<div class="step"><div class="no">STEP 02</div><h3>気になる求人に応募</h3><p>報酬・週の稼働時間・必要なスキルを求人ごとに明記。条件に合う仕事だけを選べます。</p></div>
<div class="step"><div class="no">STEP 03</div><h3>企業とチャットで相談</h3><p>応募後はzaito上のチャットで企業と直接やりとり。面談日程や働き方もここで決まります。</p></div>
</div>
<div class="coband"><div><div class="eyebrow" style="color:#8FA2FF">FOR COMPANIES</div><h2>在宅で働ける学生を、<br>採用しませんか？</h2><p>SNS運用・動画編集・事務など、完全在宅の仕事に意欲のある大学生・若手が登録しています。</p>
<div style="margin-top:28px;display:flex;gap:10px;flex-wrap:wrap"><a class="btn btn-p btn-lg" href="#">無料で求人を掲載する</a><a class="btn btn-lg" style="background:transparent;color:#fff;box-shadow:inset 0 0 0 1.5px #3A4563" href="#">資料を見る</a></div></div>
<ul><li><span class="ms">check_circle</span>初期費用・掲載費 0円（先行掲載期間）</li><li><span class="ms">check_circle</span>求人原稿の作成をサポート</li><li><span class="ms">check_circle</span>応募者とチャットで直接やりとり</li></ul></div>
</div></section>
'''
page('top.html', 'zaito | 大学生・若手向け完全在宅求人サービス', top_css, top)

# ---------------- JOBS LIST ----------------
list_css = '''
.crumb{padding-top:24px;font-size:12px;color:#6B7590;display:flex;gap:6px;align-items:center}
.crumb a{color:#6B7590}
.ph-h{display:flex;justify-content:space-between;align-items:flex-end;gap:16px;padding:16px 0 28px;border-bottom:1px solid #EEF0F4}
.ph-h h1{margin:0;font-size:clamp(26px,3vw,34px);font-weight:900}
.ph-h h1 small{font-family:Outfit,sans-serif;font-size:16px;color:#6B7590;font-weight:600;margin-left:10px}
.qs{display:flex;gap:8px;flex:0 1 460px}
.qs label{flex:1;display:flex;align-items:center;gap:8px;padding:0 14px;height:48px;border-radius:12px;box-shadow:inset 0 0 0 1.5px #D5DBE7}
.qs input{border:0;outline:0;font:inherit;font-size:14px;width:100%}
.layout{display:grid;grid-template-columns:260px 1fr;gap:40px;padding-top:32px}
.filter{align-self:start;position:sticky;top:92px;display:flex;flex-direction:column;gap:28px}
.filter h3{margin:0 0 12px;font-size:14px;font-weight:700}
.filter label{display:flex;align-items:center;gap:10px;font-size:14px;color:#3A4563;padding:6px 0}
.cb{width:20px;height:20px;border-radius:6px;box-shadow:inset 0 0 0 1.5px #CDD3E0;display:inline-flex;align-items:center;justify-content:center;flex:none}
.cb.on{background:#3D5AFE;box-shadow:none;color:#fff}
.cb.on::after{content:'check';font-family:'Material Symbols Rounded';font-size:16px;font-feature-settings:'liga'}
.filter .cnt{margin-left:auto;font-size:12px;color:#9AA2B8;font-family:Outfit,sans-serif}
.pills{display:flex;flex-wrap:wrap;gap:8px}
.pill{height:34px;padding:0 14px;border-radius:999px;display:inline-flex;align-items:center;font-size:13px;font-weight:500;box-shadow:inset 0 0 0 1.5px #D5DBE7;color:#3A4563}
.pill.on{background:#0B1530;color:#fff;box-shadow:none}
.bar{display:flex;justify-content:space-between;align-items:center;gap:12px;margin-bottom:20px;font-size:14px;color:#3A4563}
.bar .active{display:flex;gap:8px;flex-wrap:wrap}
.bar .x{display:inline-flex;align-items:center;gap:4px;height:30px;padding:0 10px 0 12px;border-radius:999px;background:#EEF1FF;color:#3D5AFE;font-size:13px;font-weight:700}
.sort{display:flex;align-items:center;gap:4px;font-weight:700;color:#0B1530}
.rows{display:flex;flex-direction:column;gap:16px}
.row{display:grid;grid-template-columns:220px 1fr auto;gap:24px;padding:16px;border:1px solid #E6E9F0;border-radius:16px;color:inherit;align-items:center}
.row .ph{aspect-ratio:4/3;border-radius:12px;overflow:hidden;background:#F4F5F8}
.row .ph img{width:100%;height:100%;object-fit:cover}
.row h3{margin:8px 0 2px;font-size:18px;line-height:1.5}
.row .co{font-size:13px;color:#6B7590}
.row .meta{display:flex;gap:18px;margin-top:12px;font-size:13px;color:#3A4563}
.row .meta span{display:inline-flex;align-items:center;gap:4px}
.row .meta .ms{font-size:18px;color:#6B7590}
.row .tags{display:flex;gap:6px;flex-wrap:wrap;margin-top:12px}
.row .side{display:flex;flex-direction:column;align-items:flex-end;gap:14px;padding-right:8px;min-width:170px}
.row .pay{font-size:12px;color:#3A4563;text-align:right}
.row .pay b{font-family:Outfit,sans-serif;font-size:28px;color:#0B1530}
.pager{display:flex;justify-content:center;gap:8px;margin-top:40px}
.pager a{width:44px;height:44px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-family:Outfit,sans-serif;font-weight:600;color:#0B1530;box-shadow:inset 0 0 0 1.5px #E6E9F0}
.pager a.on{background:#0B1530;color:#fff;box-shadow:none}
.fbtn{display:none}
@media (max-width:860px){
 .ph-h{flex-direction:column;align-items:stretch}
 .qs{flex:none}
 .layout{grid-template-columns:1fr;padding-top:16px}
 .filter{display:none}
 .fbtn{display:flex;gap:8px;overflow-x:auto;margin:0 -20px 16px;padding:0 20px}
 .fbtn .pill{flex:none}
 .row{grid-template-columns:96px 1fr;gap:14px;padding:12px}
 .row .ph{aspect-ratio:1}
 .row h3{font-size:15px;margin-top:6px}
 .row .meta{display:none}
 .row .tags{display:none}
 .row .side{grid-column:1/-1;flex-direction:row;justify-content:space-between;align-items:center;padding:10px 0 0;border-top:1px solid #EEF0F4;min-width:0}
 .row .side .btn{display:none}
 .row .pay b{font-size:22px}
 .bar .active{display:none}
}
'''
META = [('schedule', '週10時間〜'), ('event_available', 'シフト自由'), ('school', '未経験OK')]
def row(j):
    ph = f'<img src="{j["img"]}" alt="">' if 'img' in j else f'<div class="ill {j["ill"][0]}"><span class="ms">{j["ill"][1]}</span></div>'
    new = '<span class="new">NEW</span>' if j['new'] else ''
    meta = ''.join(f'<span><span class="ms">{i}</span>{t}</span>' for i, t in META)
    tags = ''.join(f'<span class="tag">{t}</span>' for t in j['tags'])
    return f'''<a class="row" href="job.html"><div class="ph">{ph}</div>
<div><div style="display:flex;gap:6px;align-items:center"><span class="cat">{j["cat"]}</span>{new}</div><h3>{j["title"]}</h3><div class="co">{j["co"]}</div>
<div class="meta">{meta}</div><div class="tags">{tags}</div></div>
<div class="side"><div class="pay">{j["unit"]} <b>{j["pay"]}</b> 円〜</div><span class="btn btn-g">詳細を見る</span></div></a>'''
def cb(label, n, on=False):
    return f'<label><span class="cb {"on" if on else ""}"></span>{label}<span class="cnt">{n}</span></label>'
lst = header('jobs') + f'''
<div class="wrap">
<div class="crumb"><a href="top.html">トップ</a><span class="ms" style="font-size:16px">chevron_right</span>求人を探す</div>
<div class="ph-h"><h1>在宅求人一覧<small>128件</small></h1>
<div class="qs"><label><span class="ms" style="color:#6B7590">search</span><input placeholder="キーワードで絞り込む"></label><button class="btn btn-p" style="height:48px">検索</button></div></div>
<div class="layout">
<aside class="filter">
<div><h3>職種</h3>{cb("SNS運用",42,True)}{cb("動画編集",31,True)}{cb("Webマーケティング",18)}{cb("ライティング",27)}{cb("オンライン事務",24)}{cb("AI関連",9)}</div>
<div><h3>週の稼働時間</h3><div class="pills"><span class="pill">〜5時間</span><span class="pill on">〜10時間</span><span class="pill">〜20時間</span><span class="pill">20時間〜</span></div></div>
<div><h3>報酬のタイプ</h3>{cb("時給",76)}{cb("成果報酬（1件・1本）",52)}</div>
<div><h3>こだわり条件</h3>{cb("未経験OK",88,True)}{cb("シフト自由",61)}{cb("土日のみOK",23)}{cb("平日夜のみOK",19)}</div>
</aside>
<div>
<div class="fbtn"><span class="pill on"><span class="ms" style="font-size:18px;margin-right:4px">tune</span>絞り込み 4</span><span class="pill">職種</span><span class="pill">稼働時間</span><span class="pill">報酬</span><span class="pill">こだわり</span></div>
<div class="bar"><div class="active"><span class="x">SNS運用<span class="ms" style="font-size:16px">close</span></span><span class="x">動画編集<span class="ms" style="font-size:16px">close</span></span><span class="x">〜10時間<span class="ms" style="font-size:16px">close</span></span><span class="x">未経験OK<span class="ms" style="font-size:16px">close</span></span></div>
<span class="sort">新着順<span class="ms" style="font-size:20px">expand_more</span></span></div>
<div class="rows">{''.join(row(j) for j in JOBS)}</div>
<div class="pager"><a class="on">1</a><a>2</a><a>3</a><a><span class="ms">chevron_right</span></a></div>
</div></div></div>'''
page('jobs.html', '在宅求人一覧 | zaito', list_css, lst)

# ---------------- JOB DETAIL ----------------
det_css = '''
.crumb{padding-top:24px;font-size:12px;color:#6B7590;display:flex;gap:6px;align-items:center}
.crumb a{color:#6B7590}
.dg{display:grid;grid-template-columns:1fr 360px;gap:56px;padding-top:24px}
.hero-img{aspect-ratio:21/9;border-radius:20px;overflow:hidden}
.hero-img img{width:100%;height:100%;object-fit:cover}
h1.t{margin:24px 0 8px;font-size:clamp(24px,3vw,34px);font-weight:900;line-height:1.4}
.coline{display:flex;align-items:center;gap:10px;font-size:14px;color:#3A4563}
.coline .av{width:32px;height:32px;border-radius:8px;background:#0B1530;color:#fff;display:flex;align-items:center;justify-content:center;font-family:Outfit,sans-serif;font-weight:700;font-size:14px}
.facts{margin-top:28px;display:grid;grid-template-columns:repeat(4,1fr);border:1px solid #E6E9F0;border-radius:16px;overflow:hidden}
.facts div{padding:18px 20px;display:flex;flex-direction:column;gap:4px}
.facts div+div{border-left:1px solid #EEF0F4}
.facts span{font-size:12px;color:#6B7590;display:flex;align-items:center;gap:4px}
.facts span .ms{font-size:16px}
.facts b{font-size:16px}
.blk{margin-top:48px}
.blk h2{margin:0 0 16px;font-size:20px;font-weight:700;display:flex;align-items:center;gap:10px}
.blk h2::before{content:'';width:4px;height:20px;border-radius:2px;background:#3D5AFE}
.blk p{margin:0;font-size:15px;color:#3A4563}
.blk ul{margin:0;padding:0;list-style:none;display:flex;flex-direction:column;gap:10px}
.blk li{display:flex;gap:10px;font-size:15px;color:#3A4563}
.blk li .ms{color:#3D5AFE;font-size:20px;margin-top:2px}
.flow{display:flex;gap:8px;align-items:center;flex-wrap:wrap}
.flow span.s{padding:10px 16px;border-radius:12px;background:#F7F8FB;font-size:14px;font-weight:700}
.flow .ms{color:#9AA2B8}
.side{align-self:start;position:sticky;top:92px;display:flex;flex-direction:column;gap:16px}
.apply{border:1px solid #E6E9F0;border-radius:20px;padding:24px;box-shadow:0 24px 48px -32px rgba(11,21,48,.35)}
.apply .pay{font-size:13px;color:#3A4563}
.apply .pay b{font-family:Outfit,sans-serif;font-size:36px;color:#0B1530;margin:0 2px}
.apply .dl{margin:18px 0;display:flex;flex-direction:column;gap:10px;font-size:14px}
.apply .dl div{display:flex;justify-content:space-between;gap:12px}
.apply .dl span{color:#6B7590}
.apply .btn{width:100%}
.apply .note{margin:12px 0 0;font-size:12px;color:#6B7590;text-align:center}
.cobox{border-radius:20px;background:#F7F8FB;padding:24px;font-size:14px;color:#3A4563}
.cobox b{display:block;color:#0B1530;font-size:16px;margin-bottom:6px}
.spbar{display:none}
@media (max-width:860px){
 .dg{grid-template-columns:1fr;gap:0}
 .hero-img{aspect-ratio:16/10;border-radius:16px}
 .facts{grid-template-columns:1fr 1fr}
 .facts div:nth-child(3){border-left:0}
 .facts div:nth-child(n+3){border-top:1px solid #EEF0F4}
 .side .apply{display:none}
 .side{position:static;margin-top:40px}
 .spbar{display:flex;position:fixed;left:0;right:0;bottom:0;z-index:60;background:#fff;border-top:1px solid #EEF0F4;padding:12px 16px calc(12px + env(safe-area-inset-bottom));gap:12px;align-items:center}
 .spbar .pay{font-size:11px;color:#3A4563;line-height:1.3}
 .spbar .pay b{font-family:Outfit,sans-serif;font-size:22px;color:#0B1530}
 .spbar .btn{flex:1;height:52px}
 body{padding-bottom:80px}
}
'''
j = JOBS[0]
det = header('jobs') + f'''
<div class="wrap">
<div class="crumb"><a href="top.html">トップ</a><span class="ms" style="font-size:16px">chevron_right</span><a href="jobs.html">求人を探す</a><span class="ms" style="font-size:16px">chevron_right</span>SNS運用</div>
<div class="dg"><div>
<div class="hero-img"><img src="{j["img"]}" alt=""></div>
<div style="display:flex;gap:6px;margin-top:24px"><span class="cat">SNS運用</span><span class="new">NEW</span></div>
<h1 class="t">{j["title"]}</h1>
<div class="coline"><span class="av">S</span>{j["co"]}<span style="color:#9AA2B8">・掲載 2日前</span></div>
<div class="facts">
<div><span><span class="ms">payments</span>報酬</span><b>時給1,500円〜</b></div>
<div><span><span class="ms">schedule</span>週の稼働</span><b>10〜15時間</b></div>
<div><span><span class="ms">home</span>勤務地</span><b>完全在宅</b></div>
<div><span><span class="ms">event</span>期間</span><b>3ヶ月以上</b></div></div>
<div class="blk"><h2>仕事内容</h2><p>自社ブランドのInstagramアカウントの運用をサポートしていただきます。投稿画像の作成（Canva）、キャプションの作成、投稿予約、週1回の数値レポートの作成が主な業務です。慣れてきたら、リール動画の企画や簡単な分析にも挑戦できます。</p></div>
<div class="blk"><h2>こんな方を歓迎します</h2><ul>
<li><span class="ms">check_circle</span>普段からInstagramをよく見ている・使っている方</li>
<li><span class="ms">check_circle</span>Canvaを触ったことがある方（未経験でもOK。最初にお教えします）</li>
<li><span class="ms">check_circle</span>授業と両立しながら、週10時間ほど継続できる方</li></ul></div>
<div class="blk"><h2>働き方</h2><ul>
<li><span class="ms">laptop_mac</span>パソコンとネット環境があれば、どこからでも作業できます</li>
<li><span class="ms">event_available</span>作業時間は自由。週1回（30分）のオンライン定例のみ日時固定です</li>
<li><span class="ms">chat</span>やりとりはzaitoのチャットとSlackで行います</li></ul></div>
<div class="blk"><h2>選考の流れ</h2><div class="flow"><span class="s">zaitoで応募</span><span class="ms">arrow_forward</span><span class="s">チャットでやりとり</span><span class="ms">arrow_forward</span><span class="s">オンライン面談（30分）</span><span class="ms">arrow_forward</span><span class="s">業務開始</span></div></div>
</div>
<aside class="side">
<div class="apply"><div class="pay">時給<b>1,500</b>円〜</div>
<div class="dl"><div><span>週の稼働</span><b>10〜15時間</b></div><div><span>勤務時間</span><b>自由（定例のみ固定）</b></div><div><span>応募締切</span><b>10月31日</b></div></div>
<a class="btn btn-p btn-lg" href="register.html">この求人に応募する</a>
<a class="btn btn-g btn-lg" style="margin-top:8px"><span class="ms" style="font-size:20px">favorite</span>気になる</a>
<p class="note">応募には無料のワーカー登録が必要です</p></div>
<div class="cobox"><b>{j["co"]}</b>SNSマーケティングの支援を行う会社です。学生スタッフ8名が在宅で活躍中。<div style="margin-top:12px"><a class="more" href="#">企業情報を見る<span class="ms" style="font-size:18px">arrow_forward</span></a></div></div>
</aside></div></div>
<div class="spbar"><div class="pay">時給<br><b>1,500</b>円〜</div><a class="btn btn-p" href="register.html">この求人に応募する</a></div>'''
page('job.html', f'{j["title"]} | zaito', det_css, det)

# ---------------- REGISTER ----------------
reg_css = '''
.rg{display:grid;grid-template-columns:1fr 480px;gap:72px;align-items:start;padding-top:64px}
.rg h1{margin:12px 0 0;font-size:clamp(28px,3.4vw,40px);font-weight:900;line-height:1.35}
.rg .sub{margin:16px 0 0;color:#3A4563}
.ben{margin-top:36px;display:flex;flex-direction:column;gap:20px}
.ben > div{display:flex;gap:16px}
.ben .ic .ms{font-size:24px}
.ben span.d{display:block;margin-top:2px}
.ben .ic{width:48px;height:48px;border-radius:14px;background:#EEF1FF;color:#3D5AFE;display:flex;align-items:center;justify-content:center;flex:none}
.ben b{display:block;font-size:16px}
.ben span.d{font-size:14px;color:#3A4563}
.card{border:1px solid #E6E9F0;border-radius:24px;padding:36px;box-shadow:0 30px 60px -40px rgba(11,21,48,.4)}
.card h2{margin:0 0 20px;font-size:20px}
.gbtn{width:100%;height:54px;border-radius:12px;box-shadow:inset 0 0 0 1.5px #D5DBE7;display:flex;align-items:center;justify-content:center;gap:10px;font-weight:700;color:#0B1530;font-size:15px}
.or{display:flex;align-items:center;gap:12px;margin:22px 0;font-size:12px;color:#9AA2B8}
.or::before,.or::after{content:'';flex:1;height:1px;background:#EEF0F4}
.f{display:flex;flex-direction:column;gap:6px;margin-bottom:16px}
.f label{font-size:13px;font-weight:700}
.f label em{font-style:normal;font-size:11px;color:#fff;background:#3D5AFE;border-radius:4px;padding:1px 6px;margin-left:6px}
.f label i{font-style:normal;font-size:11px;color:#6B7590;background:#F4F5F8;border-radius:4px;padding:1px 6px;margin-left:6px}
.f .in{height:52px;border-radius:12px;box-shadow:inset 0 0 0 1.5px #D5DBE7;display:flex;align-items:center;padding:0 16px;color:#9AA2B8;font-size:15px;justify-content:space-between}
.two{display:grid;grid-template-columns:1.4fr 1fr;gap:12px}
.agree{display:flex;gap:10px;align-items:flex-start;font-size:13px;color:#3A4563;margin:8px 0 20px}
.agree .cb{width:20px;height:20px;border-radius:6px;background:#3D5AFE;color:#fff;display:flex;align-items:center;justify-content:center;flex:none;margin-top:1px}
.card .btn{width:100%}
.card .foot{margin:18px 0 0;text-align:center;font-size:13px;color:#3A4563}
@media (max-width:860px){
 .rg{grid-template-columns:1fr;gap:32px;padding-top:32px}
 .ben{display:none}
 .card{padding:24px 20px;border-radius:20px}
}
'''
G = '<svg width="20" height="20" viewBox="0 0 48 48"><path fill="#FFC107" d="M43.6 20.1H42V20H24v8h11.3C33.7 32.7 29.2 36 24 36c-6.6 0-12-5.4-12-12s5.4-12 12-12c3.1 0 5.8 1.2 7.9 3l5.7-5.7C34 6.1 29.3 4 24 4 12.9 4 4 12.9 4 24s8.9 20 20 20 20-8.9 20-20c0-1.3-.1-2.6-.4-3.9z"/><path fill="#FF3D00" d="M6.3 14.7l6.6 4.8C14.7 15.1 19 12 24 12c3.1 0 5.8 1.2 7.9 3l5.7-5.7C34 6.1 29.3 4 24 4 16.3 4 9.7 8.3 6.3 14.7z"/><path fill="#4CAF50" d="M24 44c5.2 0 9.9-2 13.4-5.2l-6.2-5.2C29.2 35.1 26.7 36 24 36c-5.2 0-9.6-3.3-11.3-8l-6.5 5C9.5 39.6 16.2 44 24 44z"/><path fill="#1976D2" d="M43.6 20.1H42V20H24v8h11.3c-.8 2.2-2.2 4.2-4.1 5.6l6.2 5.2C37 39.2 44 34 44 24c0-1.3-.1-2.6-.4-3.9z"/></svg>'
reg = header() + f'''
<div class="wrap"><div class="rg"><div>
<div class="eyebrow">SIGN UP</div>
<h1>無料でワーカー登録して、<br>在宅の仕事をはじめよう。</h1>
<p class="sub">登録は1分。プロフィールはあとからいつでも編集できます。</p>
<div class="ben">
<div><span class="ic"><span class="ms">bolt</span></span><div><b>気になる求人にすぐ応募</b><span class="d">プロフィールを使って、ワンクリックで応募できます。</span></div></div>
<div><span class="ic"><span class="ms">notifications</span></span><div><b>希望に合う新着求人をお知らせ</b><span class="d">職種と稼働時間を登録しておくと、合う求人をメールでお届けします。</span></div></div>
<div><span class="ic"><span class="ms">chat</span></span><div><b>企業とチャットで直接やりとり</b><span class="d">面談の日程調整や質問も、zaitoの中で完結します。</span></div></div>
</div></div>
<div class="card"><h2>アカウントを作成</h2>
<a class="gbtn">{G}Googleで登録</a>
<div class="or">またはメールアドレスで登録</div>
<div class="f"><label>メールアドレス<em>必須</em></label><div class="in">you@example.com</div></div>
<div class="f"><label>パスワード<em>必須</em></label><div class="in">8文字以上<span class="ms" style="font-size:20px">visibility</span></div></div>
<div class="two"><div class="f"><label>大学名<i>任意</i></label><div class="in">例：〇〇大学</div></div>
<div class="f"><label>学年<i>任意</i></label><div class="in">選択<span class="ms" style="font-size:20px">expand_more</span></div></div></div>
<div class="agree"><span class="cb"><span class="ms" style="font-size:16px">check</span></span><span><a href="#">利用規約</a>と<a href="#">プライバシーポリシー</a>に同意する</span></div>
<a class="btn btn-p btn-lg">無料で登録する</a>
<p class="foot">アカウントをお持ちの方は <a href="#"><b>ログイン</b></a></p></div>
</div></div>'''
page('register.html', '無料ワーカー登録 | zaito', reg_css, reg)
print('ok')
