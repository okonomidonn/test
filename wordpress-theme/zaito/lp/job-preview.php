<?php
/**
 * 営業用の仮ページ（_zaito_preview=1 の求人）を、LPと同じ新デザインで表示するテンプレート。
 * inc/prelaunch.php から、求人IDを $zaito_job にセットした状態で読み込む。
 *
 * @var WP_Post $zaito_job
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$zaito_lp_url  = home_url( '/' );
$zaito_id      = $zaito_job->ID;
$zaito_company = (string) get_post_meta( $zaito_id, '_company_name', true );
$zaito_cat     = (string) get_post_meta( $zaito_id, '_job_category', true );
$zaito_emp     = (string) get_post_meta( $zaito_id, '_job_employment_type', true );
$zaito_type    = (string) get_post_meta( $zaito_id, '_job_type', true );
$zaito_days    = (string) get_post_meta( $zaito_id, '_job_days', true );
$zaito_target  = (string) get_post_meta( $zaito_id, '_job_target', true );

// 報酬: 金額があれば「時給 1,800円〜2,500円」、なければ補足文（ご相談など）を出す。
$zaito_sal_type = (string) get_post_meta( $zaito_id, '_job_salary_type', true );
$zaito_sal_min  = (string) get_post_meta( $zaito_id, '_job_salary', true );
$zaito_sal_max  = (string) get_post_meta( $zaito_id, '_job_salary_max', true );
$zaito_sal_note = (string) get_post_meta( $zaito_id, '_job_salary_note', true );
$zaito_fmt      = function ( $v ) {
    return is_numeric( $v ) ? number_format( (float) $v ) : $v;
};
$zaito_has_pay  = '' !== $zaito_sal_min;
$zaito_pay_unit = $zaito_sal_type ? $zaito_sal_type : '報酬';
$zaito_pay_main = $zaito_has_pay ? $zaito_fmt( $zaito_sal_min ) : '';
$zaito_pay_tail = $zaito_has_pay ? ( '' !== $zaito_sal_max ? '〜' . $zaito_fmt( $zaito_sal_max ) . '円' : '円〜' ) : '';
$zaito_pay_text = $zaito_has_pay ? $zaito_pay_unit . ' ' . $zaito_pay_main . $zaito_pay_tail : ( $zaito_sal_note ? $zaito_sal_note : 'ご相談' );

// 本文の末尾にある「※この説明文は…」の注記は、本文と分けて控えめに表示する。
$zaito_body = (string) $zaito_job->post_content;
$zaito_note = '';
if ( false !== strpos( $zaito_body, "\n---\n" ) ) {
    list( $zaito_body, $zaito_note ) = array_map( 'trim', explode( "\n---\n", $zaito_body, 2 ) );
}

$zaito_tags = array_values( array_filter( array_map( 'trim', preg_split( '/[、,]/u', $zaito_target ) ) ) );

// 職種ごとのイメージ（写真がない求人用のアイコンタイル）。
$zaito_icons = array(
    'SNS'       => array( 'campaign', 'a' ),
    '動画'      => array( 'movie', 'd' ),
    'マーケ'    => array( 'trending_up', 'a' ),
    'ライティング' => array( 'edit_note', 'c' ),
    'デザイン'  => array( 'palette', 'd' ),
    'プログラミング' => array( 'code', 'a' ),
    '事務'      => array( 'description', 'b' ),
    'データ入力' => array( 'keyboard', 'b' ),
    'カスタマー' => array( 'support_agent', 'b' ),
    '翻訳'      => array( 'translate', 'c' ),
    'AI'        => array( 'auto_awesome', 'd' ),
    'リサーチ'  => array( 'travel_explore', 'c' ),
);
$zaito_icon = null;
foreach ( array( $zaito_cat, $zaito_job->post_title ) as $zaito_hay ) { // 職種を優先し、なければタイトルから判定
    foreach ( $zaito_icons as $zaito_key => $zaito_val ) {
        if ( ! $zaito_icon && '' !== $zaito_hay && false !== strpos( $zaito_hay, $zaito_key ) ) {
            $zaito_icon = $zaito_val;
        }
    }
}
if ( ! $zaito_icon ) {
    $zaito_icon = array( 'work', 'a' );
}
$zaito_thumb = has_post_thumbnail( $zaito_id ) ? get_the_post_thumbnail_url( $zaito_id, 'large' ) : '';

$zaito_initial = mb_substr( preg_replace( '/^(株式会社|合同会社|有限会社|一般社団法人|一般財団法人)|(株式会社|合同会社|有限会社)$/u', '', $zaito_company ), 0, 1 );
$zaito_mail    = 'mailto:info@zaito-work.com?subject=' . rawurlencode( '掲載イメージについて（' . $zaito_company . '）' );

$zaito_facts = array(
    array( 'payments', '報酬', $zaito_pay_text ),
    array( 'home', '勤務形態', $zaito_type ? $zaito_type : '完全在宅' ),
    array( 'event_available', '勤務日数', $zaito_days ? $zaito_days : '応相談' ),
    array( 'badge', '雇用形態', $zaito_emp ? $zaito_emp : '応相談' ),
);
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="robots" content="noindex, nofollow">
<title><?php echo esc_html( $zaito_job->post_title ); ?>（掲載イメージ） | zaito</title>
<link rel="icon" href="<?php echo esc_url( get_template_directory_uri() . '/lp/favicon.svg' ); ?>" type="image/svg+xml">
<link rel="icon" href="<?php echo esc_url( get_template_directory_uri() . '/lp/favicon-32.png' ); ?>" sizes="32x32" type="image/png">
<link rel="apple-touch-icon" href="<?php echo esc_url( get_template_directory_uri() . '/lp/apple-touch-icon.png' ); ?>">
<meta name="theme-color" content="#ffffff">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;500;700;900&amp;family=Outfit:wght@500;600;700&amp;display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0&amp;display=block" rel="stylesheet">
<style>
*{box-sizing:border-box}
html{-webkit-text-size-adjust:100%}
body{margin:0;background:#fff;color:#0B1530;font-family:'Noto Sans JP',system-ui,sans-serif;-webkit-font-smoothing:antialiased;font-feature-settings:'palt';line-height:1.75}
a{color:#3D5AFE;text-decoration:none}
.ms{font-family:'Material Symbols Rounded';font-weight:400;font-style:normal;line-height:1;white-space:nowrap;font-feature-settings:'liga';display:inline-block}
.wrap{max-width:1240px;margin:0 auto;padding:0 20px}
.logo{font-family:'Outfit',sans-serif;font-weight:700;letter-spacing:-0.045em;color:#0B1530;line-height:1}
.logo span{color:#3D5AFE}
.btn{display:inline-flex;align-items:center;justify-content:center;gap:6px;height:44px;padding:0 18px;border-radius:10px;font-size:14px;font-weight:700;border:0;font-family:inherit}
.btn-p{background:#3D5AFE;color:#fff}
.btn-p:hover{background:#2A45D8;color:#fff}
.btn-g{background:#fff;color:#0B1530;box-shadow:inset 0 0 0 1.5px #D5DBE7}
.btn-lg{height:56px;padding:0 24px;border-radius:12px;font-size:16px}
.hd{position:sticky;top:0;z-index:50;background:rgba(255,255,255,.94);backdrop-filter:blur(14px);-webkit-backdrop-filter:blur(14px);border-bottom:1px solid #EEF0F4}
.hd .wrap{height:68px;display:flex;align-items:center;justify-content:space-between;gap:16px}
.hd .logo{font-size:30px}
.cat{display:inline-flex;align-items:center;height:28px;padding:0 12px;border-radius:999px;background:#EEF1FF;color:#3D5AFE;font-size:13px;font-weight:700}
.tag{display:inline-flex;align-items:center;height:30px;padding:0 12px;border-radius:999px;background:#F4F5F8;color:#3A4563;font-size:13px;font-weight:500}

.pv{margin-top:24px;display:flex;gap:16px;align-items:flex-start;background:#0B1530;color:#fff;border-radius:16px;padding:18px 22px}
.pv .ms{font-size:24px;color:#8FA2FF;margin-top:2px}
.pv b{display:block;font-size:15px}
.pv p{margin:4px 0 0;font-size:13px;color:#C9D0E4;line-height:1.7}

.dg{display:grid;grid-template-columns:minmax(0,1fr) 360px;gap:56px;padding-top:28px}
.hero-img{aspect-ratio:21/8;border-radius:20px;overflow:hidden;display:flex;align-items:center;justify-content:center}
.hero-img.ill{aspect-ratio:21/5}
.hero-img img{width:100%;height:100%;object-fit:cover}
.hero-img .ms{font-size:88px}
.ill-a{background:linear-gradient(135deg,#EEF1FF,#DCE3FF);color:#3D5AFE}
.ill-b{background:linear-gradient(135deg,#E9F7F1,#CFEFE2);color:#1A9B6C}
.ill-c{background:linear-gradient(135deg,#FFF3E6,#FFE2C2);color:#D9731A}
.ill-d{background:linear-gradient(135deg,#F3EEFF,#E2D8FF);color:#7B4DE0}
h1.t{margin:14px 0 10px;font-size:clamp(24px,3vw,34px);font-weight:900;line-height:1.4}
.coline{display:flex;align-items:center;gap:10px;font-size:14px;color:#3A4563}
.coline .av{width:32px;height:32px;border-radius:8px;background:#0B1530;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:14px;flex:none}
.facts{margin-top:28px;display:grid;grid-template-columns:repeat(4,1fr);border:1px solid #E6E9F0;border-radius:16px;overflow:hidden}
.facts div{padding:18px 20px;display:flex;flex-direction:column;gap:4px;min-width:0}
.facts div+div{border-left:1px solid #EEF0F4}
.facts span{font-size:12px;color:#6B7590;display:flex;align-items:center;gap:4px}
.facts span .ms{font-size:16px}
.facts b{font-size:15px;line-height:1.5;overflow-wrap:anywhere}
.blk{margin-top:48px}
.blk h2{margin:0 0 16px;font-size:20px;font-weight:700;display:flex;align-items:center;gap:10px}
.blk h2::before{content:'';width:4px;height:20px;border-radius:2px;background:#3D5AFE}
.blk p{margin:0 0 12px;font-size:15px;color:#3A4563}
.tags{display:flex;flex-wrap:wrap;gap:8px}
.note{margin-top:20px;padding:14px 16px;border-radius:12px;background:#F7F8FB;font-size:12px;color:#6B7590;line-height:1.7}
.flow{display:flex;gap:8px;align-items:center;flex-wrap:wrap}
.flow .s{padding:10px 16px;border-radius:12px;background:#F7F8FB;font-size:14px;font-weight:700}
.flow .ms{color:#9AA2B8}

.side{align-self:start;position:sticky;top:92px;display:flex;flex-direction:column;gap:16px}
.apply{border:1px solid #E6E9F0;border-radius:20px;padding:24px;box-shadow:0 24px 48px -32px rgba(11,21,48,.35)}
.apply .pay{font-size:13px;color:#3A4563}
.apply .pay b{font-family:'Outfit',sans-serif;font-size:36px;color:#0B1530;margin:0 2px;letter-spacing:-.01em}
.apply .pay.txt{font-size:16px;font-weight:700;color:#0B1530}
.apply .dl{margin:18px 0;display:flex;flex-direction:column;gap:10px;font-size:14px}
.apply .dl div{display:flex;justify-content:space-between;gap:12px}
.apply .dl span{color:#6B7590;flex:none}
.apply .dl b{text-align:right}
.apply .btn{width:100%;cursor:default}
.apply .cap{margin:10px 0 0;font-size:12px;color:#6B7590;text-align:center}
.ask{border-radius:20px;background:#F7F8FB;padding:24px;font-size:14px;color:#3A4563}
.ask b{display:block;color:#0B1530;font-size:16px;margin-bottom:8px}
.ask .btn{width:100%;margin-top:16px}
.spbar{display:none}

.ft{border-top:1px solid #EEF0F4;margin-top:96px}
.ft .wrap{padding-top:48px;display:flex;flex-direction:column;gap:32px}
.ft-top{display:flex;flex-wrap:wrap;justify-content:space-between;gap:24px;font-size:14px}
.ft-top p{margin:0;font-weight:700;line-height:1.8}
.ft-top nav{display:flex;flex-wrap:wrap;gap:12px 32px}
.ft-top nav a{color:#3A4563}
.ft-b{display:flex;justify-content:space-between;padding-top:20px;border-top:1px solid #EEF0F4;font-size:12px;color:#6B7590}
.ft-big{font-size:clamp(120px,24vw,340px);line-height:.78;letter-spacing:-0.065em;margin:4px 0 -0.04em -0.04em;user-select:none}

@media (max-width:860px){
 .dg{grid-template-columns:1fr;gap:0}
 .hero-img{aspect-ratio:16/8;border-radius:16px}
 .hero-img.ill{aspect-ratio:16/6}
 .hero-img .ms{font-size:64px}
 .pv{padding:16px 18px}
 .pv p{font-size:12px}
 .facts{grid-template-columns:1fr 1fr}
 .facts div:nth-child(3){border-left:0}
 .facts div:nth-child(n+3){border-top:1px solid #EEF0F4}
 .side{position:static;margin-top:40px}
 .side .apply{display:none}
 .spbar{display:flex;position:fixed;left:0;right:0;bottom:0;z-index:60;background:#fff;border-top:1px solid #EEF0F4;padding:12px 16px calc(12px + env(safe-area-inset-bottom));gap:12px;align-items:center}
 .spbar .pay{font-size:11px;color:#3A4563;line-height:1.3;max-width:40%}
 .spbar .pay b{font-family:'Outfit',sans-serif;font-size:22px;color:#0B1530}
 .spbar .btn{flex:1;height:52px}
 body{padding-bottom:84px}
 .hd .btn{padding:0 14px}
}
</style>
</head>
<body>
<header class="hd"><div class="wrap">
  <a class="logo" href="<?php echo esc_url( $zaito_lp_url ); ?>" aria-label="zaito トップへ">za<span>i</span>to</a>
  <a class="btn btn-p" href="<?php echo esc_url( $zaito_lp_url . '#contact' ); ?>">無料で求人掲載</a>
</div></header>

<main class="wrap">
  <div class="pv" role="note">
    <span class="ms" aria-hidden="true">visibility_off</span>
    <div>
      <b><?php echo esc_html( $zaito_company ? $zaito_company . ' 様向けの掲載イメージ（非公開）' : '掲載イメージ（非公開）' ); ?></b>
      <p>公開されている募集内容をもとに、zaito運営事務局が作成した掲載イメージです。一般には公開されておらず、このURLを知っている方だけが閲覧できます。内容をご確認いただき、ご了承いただけましたら正式に掲載いたします（掲載無料）。</p>
    </div>
  </div>

  <div class="dg">
    <div>
      <div class="hero-img<?php echo $zaito_thumb ? '' : ' ill ill-' . esc_attr( $zaito_icon[1] ); ?>">
        <?php if ( $zaito_thumb ) : ?>
          <img src="<?php echo esc_url( $zaito_thumb ); ?>" alt="">
        <?php else : ?>
          <span class="ms" aria-hidden="true"><?php echo esc_html( $zaito_icon[0] ); ?></span>
        <?php endif; ?>
      </div>
      <?php if ( $zaito_cat ) : ?>
        <div style="margin-top:24px"><span class="cat"><?php echo esc_html( $zaito_cat ); ?></span></div>
      <?php endif; ?>
      <h1 class="t"><?php echo esc_html( $zaito_job->post_title ); ?></h1>
      <?php if ( $zaito_company ) : ?>
        <div class="coline"><span class="av" aria-hidden="true"><?php echo esc_html( $zaito_initial ); ?></span><?php echo esc_html( $zaito_company ); ?></div>
      <?php endif; ?>

      <div class="facts">
        <?php foreach ( $zaito_facts as $zaito_f ) : ?>
          <div><span><span class="ms" aria-hidden="true"><?php echo esc_html( $zaito_f[0] ); ?></span><?php echo esc_html( $zaito_f[1] ); ?></span><b><?php echo esc_html( $zaito_f[2] ); ?></b></div>
        <?php endforeach; ?>
      </div>

      <section class="blk">
        <h2>仕事内容</h2>
        <?php echo wp_kses_post( wpautop( $zaito_body ) ); ?>
        <?php if ( $zaito_note ) : ?>
          <div class="note"><?php echo nl2br( esc_html( $zaito_note ) ); ?></div>
        <?php endif; ?>
      </section>

      <?php if ( $zaito_tags ) : ?>
        <section class="blk">
          <h2>こんな方を歓迎します</h2>
          <div class="tags">
            <?php foreach ( $zaito_tags as $zaito_t ) : ?>
              <span class="tag"><?php echo esc_html( $zaito_t ); ?></span>
            <?php endforeach; ?>
          </div>
        </section>
      <?php endif; ?>

      <section class="blk">
        <h2>zaitoでの応募の流れ</h2>
        <div class="flow"><span class="s">zaitoで応募</span><span class="ms" aria-hidden="true">arrow_forward</span><span class="s">企業とチャットでやりとり</span><span class="ms" aria-hidden="true">arrow_forward</span><span class="s">面談・選考</span><span class="ms" aria-hidden="true">arrow_forward</span><span class="s">業務開始</span></div>
      </section>
    </div>

    <aside class="side">
      <div class="apply">
        <?php if ( $zaito_has_pay ) : ?>
          <div class="pay"><?php echo esc_html( $zaito_pay_unit ); ?><b><?php echo esc_html( $zaito_pay_main ); ?></b><?php echo esc_html( $zaito_pay_tail ); ?></div>
        <?php else : ?>
          <div class="pay txt"><?php echo esc_html( $zaito_pay_text ); ?></div>
        <?php endif; ?>
        <div class="dl">
          <div><span>勤務形態</span><b><?php echo esc_html( $zaito_facts[1][2] ); ?></b></div>
          <div><span>勤務日数</span><b><?php echo esc_html( $zaito_facts[2][2] ); ?></b></div>
          <div><span>雇用形態</span><b><?php echo esc_html( $zaito_facts[3][2] ); ?></b></div>
        </div>
        <span class="btn btn-p btn-lg" aria-disabled="true">この求人に応募する</span>
        <p class="cap">掲載イメージのため、応募ボタンは動作しません</p>
      </div>
      <div class="ask">
        <b>ご確認のお願い</b>
        修正したい点があれば、お気軽にお知らせください。問題なければこのまま正式に掲載し、登録している学生へご案内します。掲載後に必要なのは、応募があった際のご返信のみです。
        <a class="btn btn-g btn-lg" href="<?php echo esc_url( $zaito_mail ); ?>"><span class="ms" style="font-size:20px" aria-hidden="true">mail</span>info@zaito-work.com へ返信</a>
      </div>
    </aside>
  </div>
</main>

<div class="spbar">
  <div class="pay"><?php if ( $zaito_has_pay ) : ?><?php echo esc_html( $zaito_pay_unit ); ?><br><b><?php echo esc_html( $zaito_pay_main ); ?></b><?php echo esc_html( $zaito_pay_tail ); ?><?php else : ?>報酬<br><b style="font-family:inherit;font-size:16px">ご相談</b><?php endif; ?></div>
  <a class="btn btn-p" href="<?php echo esc_url( $zaito_mail ); ?>">掲載について返信する</a>
</div>

<footer class="ft"><div class="wrap">
  <div class="ft-top"><p>大学生・若手向け<br>完全在宅求人サービス</p>
    <nav aria-label="フッターナビゲーション"><a href="<?php echo esc_url( $zaito_lp_url ); ?>">zaitoについて</a><a href="<?php echo esc_url( home_url( '/terms/' ) ); ?>">利用規約</a><a href="<?php echo esc_url( home_url( '/privacy/' ) ); ?>">プライバシーポリシー</a></nav></div>
  <div class="ft-b"><span>運営：zaito 運営事務局</span><small>Copyright © zaito</small></div>
  <div class="logo ft-big" aria-hidden="true">za<span>i</span>to</div>
</div></footer>
</body>
</html>
