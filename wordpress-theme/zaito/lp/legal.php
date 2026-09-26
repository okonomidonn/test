<?php
/**
 * 正式ローンチ前に、利用規約・プライバシーポリシーをLPと同じデザインで表示するテンプレート。
 * 本文は template-parts/legal-{terms|privacy}.php を通常表示と共用する。
 *
 * @var string $zaito_legal_slug  'terms' または 'privacy'
 * @var string $zaito_legal_title ページ名
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$zaito_lp_url = home_url( '/' );
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title><?php echo esc_html( $zaito_legal_title ); ?> | zaito</title>
<link rel="canonical" href="<?php echo esc_url( home_url( '/' . $zaito_legal_slug . '/' ) ); ?>">
<link rel="icon" href="<?php echo esc_url( get_template_directory_uri() . '/lp/favicon.svg' ); ?>" type="image/svg+xml">
<link rel="icon" href="<?php echo esc_url( get_template_directory_uri() . '/lp/favicon-32.png' ); ?>" sizes="32x32" type="image/png">
<link rel="apple-touch-icon" href="<?php echo esc_url( get_template_directory_uri() . '/lp/apple-touch-icon.png' ); ?>">
<meta name="theme-color" content="#ffffff">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;500;700;900&amp;family=Outfit:wght@500;600;700&amp;display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box}
html{-webkit-text-size-adjust:100%}
body{margin:0;background:#fff;color:#0B1530;font-family:'Noto Sans JP',system-ui,sans-serif;-webkit-font-smoothing:antialiased;font-feature-settings:'palt';line-height:1.75}
a{color:#3D5AFE;text-decoration:none}
a:hover{color:#2A45D8}
.logo{font-family:'Outfit',sans-serif;font-weight:700;letter-spacing:-0.045em;color:#0B1530;line-height:1}
.logo span{color:#3D5AFE}
.hd{position:sticky;top:0;z-index:50;background:rgba(255,255,255,.92);backdrop-filter:saturate(180%) blur(14px);-webkit-backdrop-filter:saturate(180%) blur(14px);border-bottom:1px solid #EEF0F4}
.hd-in{max-width:1240px;margin:0 auto;padding:0 20px;height:68px;display:flex;align-items:center;justify-content:space-between;gap:16px}
.hd-in .logo{font-size:30px}
.hd-nav{display:flex;align-items:center;gap:8px}
.hd-early{display:inline-flex;align-items:center;height:44px;padding:0 16px;font-size:14px;font-weight:700;color:#0B1530}
.hd-early:hover{color:#3D5AFE}
.hd-cta{display:inline-flex;align-items:center;justify-content:center;height:44px;padding:0 18px;border-radius:10px;background:#3D5AFE;color:#fff;font-size:14px;font-weight:700}
.hd-cta:hover{background:#2A45D8;color:#fff}
@media (max-width:560px){.hd-early{display:none}}
.doc{max-width:760px;margin:0 auto;padding:clamp(40px,7vw,80px) 20px clamp(64px,9vw,112px)}
.back{display:inline-flex;align-items:center;gap:6px;font-size:14px;font-weight:700}
.doc h1{margin:20px 0 0;font-size:clamp(28px,4vw,40px);font-weight:900;line-height:1.3;letter-spacing:-0.01em}
.doc h2{margin:48px 0 0;padding-top:28px;border-top:1px solid #EEF0F4;font-size:clamp(18px,2vw,20px);font-weight:700;line-height:1.5}
.doc p{margin:16px 0 0;font-size:15px;color:#3A4563;text-wrap:pretty;overflow-wrap:anywhere}
.doc h1 + p{margin-top:28px}
.ft{background:#fff;overflow:hidden;border-top:1px solid #EEF0F4}
.ft-in{max-width:1240px;margin:0 auto;padding:64px 20px 0;display:flex;flex-direction:column;gap:40px}
.ft-top{display:flex;flex-wrap:wrap;justify-content:space-between;align-items:flex-start;gap:32px}
.ft-top p{margin:0;font-size:14px;font-weight:700;line-height:1.8}
.ft ul{list-style:none;margin:0;padding:0;display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,auto));gap:14px 40px;font-size:14px}
.ft ul a{color:#3A4563}
.ft ul a:hover{color:#0B1530}
.ft-bottom{display:flex;flex-wrap:wrap;justify-content:space-between;gap:12px;padding-top:24px;border-top:1px solid #EEF0F4;font-size:12px;color:#6B7590}
.ft-big{font-size:clamp(120px,30vw,400px);line-height:.78;letter-spacing:-0.065em;margin:8px 0 -0.04em -0.04em;user-select:none}
</style>
</head>
<body>
<header class="hd">
  <div class="hd-in">
    <a class="logo" href="<?php echo esc_url( $zaito_lp_url ); ?>" aria-label="zaito トップへ">za<span>i</span>to</a>
    <div class="hd-nav">
      <a class="hd-early" href="<?php echo esc_url( $zaito_lp_url . '#early' ); ?>">先行登録</a>
      <a class="hd-cta" href="<?php echo esc_url( $zaito_lp_url . '#contact' ); ?>">無料で求人掲載</a>
    </div>
  </div>
</header>

<main class="doc">
  <a class="back" href="<?php echo esc_url( $zaito_lp_url ); ?>">← トップへ戻る</a>
  <?php get_template_part( 'template-parts/legal', $zaito_legal_slug ); ?>
</main>

<footer class="ft">
  <div class="ft-in">
    <div class="ft-top">
      <p>大学生・若手向け<br>完全在宅求人サービス</p>
      <nav aria-label="フッターナビゲーション">
        <ul>
          <li><a href="<?php echo esc_url( $zaito_lp_url . '#about' ); ?>">サービスについて</a></li>
          <li><a href="<?php echo esc_url( $zaito_lp_url . '#companies' ); ?>">企業の方</a></li>
          <li><a href="<?php echo esc_url( $zaito_lp_url . '#contact' ); ?>">お問い合わせ</a></li>
          <li><a href="<?php echo esc_url( home_url( '/terms/' ) ); ?>">利用規約</a></li>
          <li><a href="<?php echo esc_url( home_url( '/privacy/' ) ); ?>">プライバシーポリシー</a></li>
        </ul>
      </nav>
    </div>
    <div class="ft-bottom">
      <span>運営：zaito 運営事務局</span>
      <small style="font-size:12px">Copyright © zaito</small>
    </div>
    <div class="logo ft-big" aria-hidden="true">za<span>i</span>to</div>
  </div>
</footer>
</body>
</html>
