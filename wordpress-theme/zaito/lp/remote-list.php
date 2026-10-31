<?php
/**
 * まとめ求人の一覧。トップページ（/）として inc/prelaunch.php から、
 * 条件別の一覧（/zaitaku/{slug}/）として inc/remote-jobs.php から（$zaito_list_cond をセットして）読み込む。
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
require_once __DIR__ . '/remote-parts.php';

$zaito_cond    = isset( $zaito_list_cond ) ? zaito_remote_conditions()[ $zaito_list_cond ] : null;
$zaito_jobs    = $zaito_cond ? zaito_remote_condition_jobs( $zaito_list_cond ) : zaito_remote_jobs();
$zaito_actives = zaito_remote_active_conditions();
$zaito_open   = array_values( array_filter( $zaito_jobs, function ( $j ) {
    return empty( $j['closed'] );
} ) );
$zaito_closed = array_values( array_filter( $zaito_jobs, function ( $j ) {
    return ! empty( $j['closed'] );
} ) );

// 条件の書き添えがない求人、報酬が書かれている求人を先に並べる（同じ条件の中では元の順番）。
// トップページでは、その前に大学生が応募しやすい順に並べる（社会人・経験者向けの求人は消さずに下へ）。
// 報酬が書かれていない求人（Wantedlyに多い）は、学生向けでも、報酬が書かれた学生向け・未経験OKの求人のあと（社会人向けの前）に回す。
$zaito_rank = function ( $j ) use ( $zaito_cond ) {
    $unpaid = '' === $j['pay'] || false !== strpos( $j['pay'], '募集ページで確認' );
    if ( $zaito_cond ) {
        return ( empty( $j['caution'] ) ? 0 : 2 ) + ( $unpaid ? 1 : 0 );
    }
    $tier = zaito_remote_student_rank( $j );
    return ( $tier < 3 && $unpaid ? 25 : $tier * 10 ) + ( empty( $j['caution'] ) ? 0 : 2 ) + ( $unpaid ? 1 : 0 );
};
foreach ( $zaito_open as $zaito_i => $zaito_j ) {
    $zaito_open[ $zaito_i ]['_order'] = $zaito_i;
}
usort( $zaito_open, function ( $a, $b ) use ( $zaito_rank ) {
    $d = $zaito_rank( $a ) - $zaito_rank( $b );
    return $d ? $d : $a['_order'] - $b['_order'];
} );

// トップページでは、学生OK・経験なしで始められて時給の高い求人を最初の3件に置く（エンジニア・営業などの「その他」と、時給が書かれていない求人は除く。1社1件まで）。
if ( ! $zaito_cond ) {
    $zaito_cands = array_filter( $zaito_open, function ( $j ) {
        $g = zaito_remote_group( $j['category'] );
        return 0 === zaito_remote_student_rank( $j )
            && zaito_remote_hourly_min( $j['pay'] ) > 0
            && ! in_array( $g[0], array( 'エンジニア', 'その他' ), true );
    } );
    usort( $zaito_cands, function ( $a, $b ) {
        $d = zaito_remote_hourly_min( $b['pay'] ) - zaito_remote_hourly_min( $a['pay'] );
        return $d ? $d : $a['_order'] - $b['_order'];
    } );
    $zaito_picks = array();
    foreach ( $zaito_cands as $zaito_j ) {
        if ( count( $zaito_picks ) < 3 && ! isset( $zaito_picks[ $zaito_j['company'] ] ) ) {
            $zaito_picks[ $zaito_j['company'] ] = $zaito_j['slug'];
        }
    }
    $zaito_front = array();
    $zaito_rest  = array();
    foreach ( $zaito_open as $zaito_j ) {
        if ( in_array( $zaito_j['slug'], $zaito_picks, true ) ) {
            $zaito_j['_pick'] = true;
            $zaito_front[ array_search( $zaito_j['slug'], array_values( $zaito_picks ), true ) ] = $zaito_j;
        } else {
            $zaito_rest[] = $zaito_j;
        }
    }
    ksort( $zaito_front );
    $zaito_open = array_merge( array_values( $zaito_front ), $zaito_rest );
}

// 分類ごとの件数（絞り込みボタンに表示）。
$zaito_groups = array();
foreach ( $zaito_open as $zaito_j ) {
    $zaito_g = zaito_remote_group( $zaito_j['category'] );
    if ( ! isset( $zaito_groups[ $zaito_g[0] ] ) ) {
        $zaito_groups[ $zaito_g[0] ] = array( 'icon' => $zaito_g[1], 'tone' => $zaito_g[2], 'n' => 0 );
    }
    $zaito_groups[ $zaito_g[0] ]['n']++;
}
$zaito_checked  = max( array_map( function ( $j ) {
    return $j['checked'];
}, $zaito_jobs ) );

if ( $zaito_cond ) {
    $zaito_title = $zaito_cond['title'] . ' | zaito';
    $zaito_desc  = mb_substr( $zaito_cond['body'][0], 0, 110 ) . '（' . count( $zaito_jobs ) . '件・運営が在宅の条件を確認済み）';
    $zaito_canon = zaito_remote_condition_url( $zaito_list_cond );
} else {
    $zaito_title = '在宅バイト・完全在宅の求人｜出社なしの仕事だけを集めたzaito';
    $zaito_desc  = '「在宅バイト」で探しても出社ありが混ざる。zaitoは、自宅だけで働ける完全在宅のバイト・パート・長期インターンを、運営が1件ずつ確認してまとめています。学生OK・未経験OKの求人も。事務、カスタマーサポート、SNS運用、ライター、エンジニアなど。';
    $zaito_canon = zaito_remote_url();
}

$zaito_items = array();
foreach ( $zaito_open as $zaito_i => $zaito_j ) {
    $zaito_items[] = array(
        '@type'    => 'ListItem',
        'position' => $zaito_i + 1,
        'url'      => zaito_remote_url( $zaito_j['slug'] ),
        'name'     => $zaito_j['title'] . '（' . $zaito_j['company'] . '）',
    );
}
$zaito_faq    = $zaito_cond && function_exists( 'zaito_remote_condition_faq' ) ? zaito_remote_condition_faq( $zaito_list_cond, $zaito_jobs ) : array();
$zaito_ld     = array(
    '@type'           => 'ItemList',
    'name'            => $zaito_cond ? $zaito_cond['h1'] : '完全在宅の求人',
    'itemListElement' => $zaito_items,
);
if ( $zaito_faq ) {
    $zaito_ld = array(
        '@context' => 'https://schema.org',
        '@graph'   => array(
            $zaito_ld,
            array(
                '@type'      => 'FAQPage',
                'mainEntity' => array_map( function ( $qa ) {
                    return array(
                        '@type'          => 'Question',
                        'name'           => $qa[0],
                        'acceptedAnswer' => array( '@type' => 'Answer', 'text' => trim( preg_replace( '/\s+/u', ' ', wp_strip_all_tags( str_replace( '</p>', "</p>\n", $qa[1] ) ) ) ) ),
                    );
                }, $zaito_faq ),
            ),
        ),
    );
} else {
    $zaito_ld = array( '@context' => 'https://schema.org' ) + $zaito_ld;
}
zaito_remote_head( $zaito_title, $zaito_desc, $zaito_canon, $zaito_ld );

$zaito_card = function ( $j ) {
    $g       = zaito_remote_group( $j['category'] );
    $pay     = zaito_remote_pay_parts( $j['pay'] );
    $initial = mb_substr( preg_replace( '/^(株式会社|合同会社|有限会社|学校法人)|(株式会社|合同会社|有限会社)$/u', '', $j['company'] ), 0, 1 );
    ?>
    <li class="card t-<?php echo esc_attr( $g[2] ); ?><?php echo ! empty( $j['closed'] ) ? ' closed' : ''; ?>" data-group="<?php echo esc_attr( $g[0] ); ?>" data-slug="<?php echo esc_attr( $j['slug'] ); ?>"<?php echo zaito_remote_card_attrs( $j ); // 数値と0/1だけ ?>>
      <a href="<?php echo esc_url( zaito_remote_url( $j['slug'] ) ); ?>">
        <div class="band"><span class="ms" aria-hidden="true"><?php echo esc_html( $g[1] ); ?></span><span class="bcat"><?php echo esc_html( $j['category'] ); ?></span><?php if ( ! empty( $j['_pick'] ) ) : ?><span class="pickb">未経験OK・高時給</span><?php endif; ?></div>
        <div class="body">
          <h2><?php echo esc_html( $j['title'] ); ?></h2>
          <span class="co"><span class="av" aria-hidden="true"><?php echo esc_html( $initial ); ?></span><?php echo esc_html( $j['company'] ); ?></span>
          <div class="pay">
            <?php if ( $pay ) : ?>
              <span class="u"><?php echo esc_html( $pay[0] ); ?></span><b><?php echo esc_html( $pay[1] ); ?></b><span class="u"><?php echo esc_html( $pay[2] ); ?></span>
            <?php elseif ( '' !== $j['pay'] ) : ?>
              <span class="tx"><?php echo esc_html( $j['pay'] ); ?></span>
            <?php else : ?>
              <span class="none">報酬は募集ページで確認</span>
            <?php endif; ?>
          </div>
          <?php if ( $j['hours'] ) : ?>
            <p class="hrs"><span class="ms" aria-hidden="true">schedule</span><span><?php echo esc_html( $j['hours'] ); ?></span></p>
          <?php endif; ?>
          <div class="tags">
            <?php if ( ! empty( $j['closed'] ) ) : ?>
              <span class="warn">募集終了</span>
            <?php else : ?>
              <span class="ok"><span class="ms" aria-hidden="true">verified</span>完全在宅</span>
            <?php endif; ?>
            <?php if ( ! empty( $j['contract'] ) ) : ?>
              <span class="kt"><?php echo esc_html( $j['contract'] ); ?></span>
            <?php endif; ?>
            <span class="tg"><?php echo esc_html( $j['target'] ); ?></span>
          </div>
        </div>
      </a>
      <?php if ( empty( $j['closed'] ) ) : ?>
        <button type="button" class="fav u022" data-fav="<?php echo esc_attr( $j['slug'] ); ?>" aria-pressed="false" aria-label="<?php echo esc_attr( $j['title'] ); ?>を保存"><?php echo zaito_u022_heart(); // 固定のSVGのみ ?></button>
      <?php endif; ?>
    </li>
    <?php
};
?>
<body>
<?php
if ( ! $zaito_cond ) :
    // トップページのお知らせの帯（lp/u073.*）。新着は、最後に追加した日から2週間だけ出す。
    $zaito_last  = max( array_map( function ( $j ) {
        return ! empty( $j['listed'] ) ? $j['listed'] : $j['checked'];
    }, $zaito_open ) );
    $zaito_new_n = count( array_filter( $zaito_open, function ( $j ) use ( $zaito_last ) {
        return ( ! empty( $j['listed'] ) ? $j['listed'] : $j['checked'] ) === $zaito_last;
    } ) );
    $zaito_news  = array();
    if ( strtotime( $zaito_last ) >= strtotime( current_time( 'Y-m-d' ) . ' -14 days' ) ) {
        $zaito_news[] = array( '求人を' . $zaito_new_n . '件追加しました', date_i18n( 'n月j日', strtotime( $zaito_last ) ) . '・全' . count( $zaito_open ) . '件', home_url( '/?sort=new#zcards' ) );
    }
    $zaito_news[] = array( '気になる求人を保存できます', 'マイページでいつでも見られます', home_url( '/mypage/' ) );
    $zaito_news[] = array( '時給や学生OKで絞り込み', '押す前に件数が分かります', '#zrefine' );
    $zaito_news[] = array( '出社なしの求人だけを掲載', '運営が確かめていること', home_url( '/kijun/' ) );
    $zaito_news_json = wp_json_encode( $zaito_news, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
    ?>
<link rel="stylesheet" href="<?php echo esc_url( zaito_lp_asset( 'u073.css' ) ); ?>">
<div class="u073" data-items="<?php echo esc_attr( $zaito_news_json ); ?>" data-ver="<?php echo esc_attr( substr( md5( $zaito_news_json ), 0, 10 ) ); ?>"><div>
  <div class="u073-bar" role="region" aria-label="お知らせ" aria-roledescription="切り替わるお知らせ">
    <div class="u073-in">
      <span class="u073-tag">お知らせ</span>
      <div class="u073-msgs" aria-live="polite"></div>
      <span class="u073-segs" aria-hidden="true"></span>
      <button class="u073-b u073-nx" type="button" aria-label="次のお知らせ"><svg viewBox="0 0 14 14"><path d="M5 3l4 4-4 4"/></svg></button>
      <button class="u073-b u073-x" type="button" aria-label="お知らせを閉じる"><svg viewBox="0 0 14 14"><path d="M3.5 3.5l7 7M10.5 3.5l-7 7"/></svg></button>
    </div>
  </div>
</div></div>
<?php endif; ?>
<?php zaito_remote_header(); ?>

<section class="hero">
  <div class="wrap">
    <div class="hero-in">
      <div class="hero-copy">
        <?php if ( $zaito_cond ) : ?>
          <p class="crumb"><a href="<?php echo esc_url( home_url( '/' ) ); ?>">完全在宅の求人</a> ／ <?php echo esc_html( $zaito_cond['label'] ); ?></p>
          <h1><?php echo esc_html( $zaito_cond['h1'] ); ?></h1>
          <?php foreach ( $zaito_cond['body'] as $zaito_para ) : ?>
            <p><?php echo esc_html( $zaito_para ); ?></p>
          <?php endforeach; ?>
        <?php else : ?>
        <span class="eb">REMOTE JOBS</span>
        <h1>完全在宅の仕事だけを、<br>集めました。</h1>
        <p><b>研修も会議も、出社なし。</b>「在宅OK」で探すと出社ありの求人が混ざるため、zaitoは運営が1件ずつ確かめて載せています。<a href="<?php echo esc_url( home_url( '/kijun/' ) ); ?>">確認していること</a></p>
        <?php endif; ?>
        <a class="alert-link" href="#signup"><span class="ms" aria-hidden="true">notifications</span>新しい在宅求人をメールで受け取る</a>
      </div>
      <?php if ( ! $zaito_cond ) : ?>
        <?php // 読み込み中の形を最初から出しておき、おすすめが届いたときに下の一覧がずれないようにする（JSが動かないときは隠す）。 ?>
        <noscript><style>#zreco{display:none}</style></noscript>
        <section class="reco is-loading" id="zreco" data-ajax="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" aria-labelledby="zreco-t">
          <div class="reco-hd">
            <h2 id="zreco-t"><span class="ms" aria-hidden="true">auto_awesome</span>今日のおすすめ</h2>
            <span class="reco-n" aria-live="polite"></span>
          </div>
          <p class="reco-sub">あなたに合う求人を選んでいます…</p>
          <div class="reco-deck">
            <div class="sw-stack"><div class="reco-skel" aria-hidden="true"><span></span><span></span><span></span></div></div>
            <div class="reco-end" hidden></div>
            <div class="reco-gate" hidden role="dialog" aria-labelledby="zrecogt">
              <span class="ms reco-gate-i" aria-hidden="true">mark_email_unread</span>
              <p class="reco-gate-t" id="zrecogt">今日の5件を見終わりました</p>
              <p>無料登録すると、次の5件と、あなたの希望に合うおすすめが毎日見られます。保存した求人は、登録後もそのまま残ります。</p>
              <form novalidate>
                <label class="sr" for="zrecoemail">メールアドレス</label>
                <input type="email" id="zrecoemail" required autocomplete="email" inputmode="email" placeholder="example@gmail.com">
                <button type="submit" class="btn btn-p">無料で登録する（メールだけ）</button>
              </form>
              <p class="err" role="alert"></p>
              <button type="button" class="reco-gate-x">登録せずに一覧を見る</button>
            </div>
          </div>
          <div class="sw-act">
            <button type="button" class="sw-b sw-no" aria-label="スキップ"><span class="ms" aria-hidden="true">close</span></button>
            <a class="sw-more" href="#">詳しく見る</a>
            <button type="button" class="sw-b sw-yes" aria-label="気になる"><span class="ms" aria-hidden="true">favorite</span></button>
          </div>
        </section>
      <?php endif; ?>
    </div>

    <?php if ( ! $zaito_cond && count( $zaito_groups ) > 1 ) : ?>
      <div class="filters" role="group" aria-label="職種で絞り込む">
        <button type="button" data-filter="" aria-pressed="true"><span class="ms" aria-hidden="true">apps</span>すべて<small><?php echo esc_html( count( $zaito_open ) ); ?></small></button>
        <?php foreach ( $zaito_groups as $zaito_name => $zaito_g ) : ?>
          <button type="button" class="t-<?php echo esc_attr( $zaito_g['tone'] ); ?>" data-filter="<?php echo esc_attr( $zaito_name ); ?>" aria-pressed="false"><span class="ms" aria-hidden="true"><?php echo esc_html( $zaito_g['icon'] ); ?></span><?php echo esc_html( $zaito_name ); ?><small><?php echo esc_html( $zaito_g['n'] ); ?></small></button>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
    <div class="refine" id="zrefine" role="group" aria-label="条件で絞り込む">
      <span class="rl">条件</span>
      <div class="qchips">
        <button type="button" data-k="who" data-v="student" aria-pressed="false">学生OK<small></small></button>
        <button type="button" data-k="who" data-v="adult" aria-pressed="false">社会人・主婦OK<small></small></button>
        <button type="button" data-k="newbie" data-v="1" aria-pressed="false">未経験OK<small></small></button>
        <button type="button" data-k="days" data-v="2" aria-pressed="false">週1〜2日でOK<small></small></button>
        <button type="button" data-k="pay" data-v="1200" aria-pressed="false">時給1,200円〜<small></small></button>
        <button type="button" data-k="pay" data-v="1500" aria-pressed="false">時給1,500円〜<small></small></button>
        <button type="button" data-k="pay" data-v="2000" aria-pressed="false">時給2,000円〜<small></small></button>
        <button type="button" class="qfav" data-k="fav" data-v="1" aria-pressed="false"><span class="ms" aria-hidden="true">bookmark</span>保存した求人<small></small></button>
      </div>
    </div>
  </div>
</section>

<main class="wrap">
  <div class="count-bar">
    <p class="count" aria-live="polite"><b id="zcount"><?php echo esc_html( count( $zaito_open ) ); ?></b>件の求人<span>・<?php echo esc_html( date_i18n( 'n月j日', strtotime( $zaito_checked ) ) ); ?>に運営が確認</span></p>
    <button type="button" class="clear" id="zclear" hidden><span class="ms" aria-hidden="true">close</span>条件をクリア</button>
    <label class="sort"><span class="sr">並び替え</span><span class="ms" aria-hidden="true">swap_vert</span><select id="zsort">
      <option value="">おすすめ順</option>
      <option value="pay">時給が高い順</option>
      <option value="new">新着順</option>
    </select></label>
  </div>
  <ul class="cards" id="zcards">
    <?php foreach ( $zaito_open as $zaito_i => $zaito_j ) : ?>
      <?php $zaito_card( $zaito_j ); ?>
      <?php if ( 2 === $zaito_i ) : // 3件目のあとに、スクロールしなくても目に入る登録欄を置く ?>
        <li class="mini-cta" id="zmini">
          <div class="mc-copy">
            <span class="ms" aria-hidden="true">mark_email_unread</span>
            <p><b>希望に合う新着だけ、メールで受け取る</b>メールアドレスだけで登録できます。無料です。</p>
          </div>
          <form id="zminiform" novalidate>
            <label class="sr" for="zminiemail">メールアドレス</label>
            <input type="email" id="zminiemail" required autocomplete="email" inputmode="email" placeholder="example@gmail.com">
            <button type="submit" class="btn btn-p">無料で登録</button>
          </form>
          <p class="err" id="zminierr" role="alert"></p>
        </li>
      <?php endif; ?>
    <?php endforeach; ?>
  </ul>
  <?php // スマホではページがとても長くなり、下の登録欄までたどり着く人が少ないため、最初は20件だけ見せる（JSが動かないときは全件）。 ?>
  <div class="more" id="zmore" hidden><button type="button" class="btn btn-g btn-lg" id="zmorebtn">もっと見る（残り<span id="zmoren"></span>件）</button></div>
  <p class="none" id="znone" hidden>条件に合う求人がありません。<button type="button" id="znone-reset">条件をリセット</button></p>

  <?php if ( $zaito_closed ) : ?>
    <h2 class="sub">募集が終了した求人</h2>
    <ul class="cards">
      <?php foreach ( $zaito_closed as $zaito_j ) { $zaito_card( $zaito_j ); } ?>
    </ul>
  <?php endif; ?>

  <?php if ( $zaito_faq ) : ?>
    <section class="cond-faq doc" aria-labelledby="cond-faq-title">
      <h2 id="cond-faq-title">大学生の在宅バイト よくある質問</h2>
      <?php foreach ( $zaito_faq as $zaito_qa ) : ?>
        <h3><?php echo esc_html( $zaito_qa[0] ); ?></h3>
        <?php echo wp_kses_post( $zaito_qa[1] ); ?>
      <?php endforeach; ?>
      <p>ほかの質問は<a href="<?php echo esc_url( home_url( '/faq/' ) ); ?>">よくある質問</a>をご覧ください。</p>
    </section>
  <?php endif; ?>

  <?php if ( $zaito_actives ) : ?>
    <nav class="conds" aria-labelledby="conds-title">
      <h2 id="conds-title">条件で探す</h2>
      <div class="conds-list">
      <?php foreach ( $zaito_actives as $zaito_cs => $zaito_cn ) : $zaito_cd = zaito_remote_conditions()[ $zaito_cs ]; ?>
        <a href="<?php echo esc_url( zaito_remote_condition_url( $zaito_cs ) ); ?>"<?php echo isset( $zaito_list_cond ) && $zaito_list_cond === $zaito_cs ? ' aria-current="page"' : ''; ?>><span class="ms" aria-hidden="true"><?php echo esc_html( $zaito_cd['icon'] ); ?></span><?php echo esc_html( $zaito_cd['label'] ); ?><small><?php echo esc_html( $zaito_cn ); ?></small></a>
      <?php endforeach; ?>
      </div>
    </nav>
  <?php endif; ?>

  <section class="signup-band" id="signup" aria-labelledby="signup-title">
    <div class="sb-copy">
      <span class="ms" aria-hidden="true">mark_email_unread</span>
      <h2 id="signup-title">新しい在宅求人を、<br>メールで受け取る</h2>
      <p>求人は随時追加しています。登録すると、やってみたい仕事・働ける日数・希望の時給に合う新しい求人だけをメールでお知らせします。登録は無料です。配信をやめたいときは、メールの停止リンクか返信ですぐに止められます。</p>
    </div>
    <div class="sb-form">
      <form id="ztop" method="post" action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" novalidate>
        <input type="hidden" name="action" value="zaito_remote_register">
        <input type="hidden" name="job" value="">
        <input type="hidden" name="utm_source" value=""><input type="hidden" name="utm_medium" value=""><input type="hidden" name="utm_campaign" value=""><input type="hidden" name="referrer" value="">
        <div class="hp" aria-hidden="true"><label>Webサイト<input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>
        <?php if ( zaito_social_buttons( '/?signup=done', '登録' ) ) : ?><div class="or-line">または</div><?php endif; ?>
        <label for="ztopemail">メールアドレス</label>
        <div class="sb-row">
          <input type="email" id="ztopemail" name="email" required autocomplete="email" inputmode="email" placeholder="example@gmail.com">
          <button type="submit" class="btn btn-p btn-lg">無料で登録する</button>
        </div>
        <p class="legal">登録すると、<a href="<?php echo esc_url( home_url( '/terms/' ) ); ?>">利用規約</a>と<a href="<?php echo esc_url( home_url( '/privacy/' ) ); ?>">プライバシーポリシー</a>に同意したものとみなします。</p>
        <p class="err" id="ztoperr" role="alert"></p>
      </form>
      <form class="profile" id="zprofile" data-steps method="post" action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" hidden>
        <b>登録できました</b>
        <p>あなたの希望に合う新着だけをメールで送るために教えてください（お名前以外は任意）。答えた内容に合わない求人は送りません。</p>
        <input type="hidden" name="action" value="zaito_remote_profile">
        <?php zaito_remote_profile_fields( true ); ?>
        <button type="submit" class="btn btn-p btn-lg">保存する</button>
        <button type="button" class="skip" id="zskip">答えずに閉じる</button>
      </form>
<link rel="stylesheet" href="<?php echo esc_url( zaito_lp_asset( 'u063.css' ) ); ?>">
<script src="<?php echo esc_url( zaito_lp_asset( 'u063.js' ) ); ?>"></script>
      <div class="sb-done" id="zdone" hidden>
        <span class="ms" aria-hidden="true">check_circle</span>
        <b id="zdonetitle">登録が完了しました</b>
        <p id="zdonetext">確認メールを送りました。メールのリンクを押すと、新しい在宅求人のお知らせが届くようになります。気になる求人は「応募ページへ進む」からそのまま応募できます。</p>
        <?php zaito_share_box( '出社なしの在宅バイト・インターンだけを集めたサイト。学生OKの求人もあるよ', home_url( '/' ), '在宅バイトを探している友達に教える' ); ?>
      </div>
    </div>
  </section>

  <section class="how" aria-labelledby="how-title">
    <h2 id="how-title">zaitoの使い方</h2>
    <ol>
      <li><span class="ms" aria-hidden="true">search</span><b>求人を探す</b><span>載っているのは、運営が出社の有無を確かめた求人だけです。</span></li>
      <li><span class="ms" aria-hidden="true">mail</span><b>無料で登録</b><span>メールアドレスだけで登録でき、新しい求人もお知らせします。</span></li>
      <li><span class="ms" aria-hidden="true">open_in_new</span><b>募集ページから応募</b><span>応募と選考は、各企業の募集ページ（Wantedlyなど）で行います。</span></li>
    </ol>
  </section>

  <a class="biz" href="<?php echo esc_url( home_url( '/for-companies/' ) ); ?>">
    <span><b>在宅で働く人を募集している企業の方へ</b>zaitoへの求人掲載は無料です。求人原稿の作成もお手伝いします。</span>
    <span class="ms" aria-hidden="true">arrow_forward</span>
  </a>

  <div class="disc">
    <b>この一覧について</b><br>
    企業が公開している募集ページをもとに、zaito運営が会社名・職種・報酬・稼働条件・在宅の条件をまとめたものです。応募や選考は各企業の募集ページで行われ、zaitoは応募を受け付けたり取り次いだりしません。募集内容は変わることがあるため、応募前に必ず元のページをご確認ください。掲載内容の修正・削除をご希望の企業の方は、<a href="mailto:info@zaito-work.com">info@zaito-work.com</a> までご連絡ください。
  </div>
</main>

<?php zaito_remote_footer(); ?>
<link rel="stylesheet" href="<?php echo esc_url( zaito_lp_asset( 'u022.css' ) ); ?>">
<script src="<?php echo esc_url( zaito_lp_asset( 'u022.js' ) ); ?>"></script>
<?php if ( ! $zaito_cond ) : ?><script src="<?php echo esc_url( zaito_lp_asset( 'u073.js' ) ); ?>"></script><?php endif; ?>
<?php if ( ! $zaito_cond ) : ?><script src="<?php echo esc_url( zaito_lp_asset( 'reco.js' ) ); ?>"></script><?php endif; ?>
<div class="sbar" id="zbar" hidden>
  <p><b>新着の在宅求人をメールで</b>登録無料・メールアドレスだけ</p>
  <a class="btn btn-p" href="#signup" id="zbargo">登録する</a>
  <button type="button" class="sbar-x" id="zbarx" aria-label="閉じる"><span class="ms" aria-hidden="true">close</span></button>
</div>
<script>
(function () {
  // 絞り込み: 職種（1つ）＋条件のチップ（応募できる人・時給はどれか1つ、ほかはオン/オフ）＋並び替え。
  // 求人に書かれていない項目（日数が分からないなど）は、その条件を選んだときは出さない。
  // 選んだ条件はURLに残し、共有や戻るボタンでも同じ絞り込みで開けるようにする。
  var list = document.getElementById('zcards');
  var cards = Array.prototype.slice.call(list.querySelectorAll('.card'));
  cards.forEach(function (c, i) { c.dataset.i = i; });
  var groupBtns = document.querySelectorAll('.filters button');
  var chips = document.querySelectorAll('.qchips button');
  var count = document.getElementById('zcount');
  var none = document.getElementById('znone');
  var clearBtn = document.getElementById('zclear');
  var sortSel = document.getElementById('zsort');
  var st = { g: '', who: '', pay: 0, days: 0, newbie: 0, fav: 0 };
  var LIMIT = 20, expanded = false;
  var more = document.getElementById('zmore');
  function favs() { try { return JSON.parse(localStorage.getItem('zaito_favs') || '[]'); } catch (e) { return []; } }
  function match(d, s, saved) {
    return (!s.g || d.group === s.g)
      && (!s.who || (s.who === 'student' ? d.student === '1' : d.adult === '1'))
      && (!s.pay || +d.pay >= s.pay)
      && (!s.days || (+d.days > 0 && +d.days <= s.days))
      && (!s.newbie || d.newbie === '1')
      && (!s.fav || saved.indexOf(d.slug) !== -1);
  }
  function countWith(patch, saved) {
    var s = Object.assign({}, st, patch), n = 0;
    cards.forEach(function (c) { if (match(c.dataset, s, saved)) n++; });
    return n;
  }
  function sortCards() {
    var by = sortSel.value;
    var sorted = cards.slice().sort(function (a, b) {
      var x = a.dataset, y = b.dataset;
      if (by === 'pay' && +y.pay !== +x.pay) return +y.pay - +x.pay;
      if (by === 'new' && +y.listed !== +x.listed) return +y.listed - +x.listed;
      return x.i - y.i;
    });
    sorted.forEach(function (c) { list.appendChild(c); });
  }
  function apply(push) {
    var saved = favs(), n = 0, shown = [];
    list.querySelectorAll('.card').forEach(function (c) {
      var show = match(c.dataset, st, saved);
      c.style.display = show ? '' : 'none';
      if (show) { n++; shown.push(c); }
    });
    // 「もっと見る」を押すまでは、条件に合う求人のうち最初の20件だけ見せる。
    shown.forEach(function (c, i) { if (!expanded && i >= LIMIT) c.style.display = 'none'; });
    more.hidden = expanded || shown.length <= LIMIT;
    document.getElementById('zmoren').textContent = Math.max(0, shown.length - LIMIT);
    // 登録の案内は、表示中の3件目のあとに置き直す（少ないときは最後）。
    var mini = document.getElementById('zmini');
    if (mini) {
      var anchor = shown[Math.min(2, shown.length - 1)];
      if (anchor) anchor.after(mini); else list.appendChild(mini);
    }
    count.textContent = n;
    if (none) none.hidden = n > 0;
    groupBtns.forEach(function (b) {
      var g = b.getAttribute('data-filter');
      b.setAttribute('aria-pressed', g === st.g ? 'true' : 'false');
      var sm = b.querySelector('small');
      if (sm) sm.textContent = countWith({ g: g }, saved);
    });
    // 条件のチップには「押したら何件になるか」を出し、0件になるものは薄くする。
    chips.forEach(function (b) {
      var k = b.getAttribute('data-k'), v = b.getAttribute('data-v');
      var on = String(st[k]) === v;
      var p = {}; p[k] = k === 'who' ? v : +v;
      var c = on ? n : countWith(p, saved);
      b.setAttribute('aria-pressed', on ? 'true' : 'false');
      b.querySelector('small').textContent = c;
      b.classList.toggle('zero', !on && c === 0);
    });
    var active = st.g || st.who || st.pay || st.days || st.newbie || st.fav;
    clearBtn.hidden = !active;
    if (push !== false && window.history.replaceState) {
      var q = new URLSearchParams(location.search);
      ['g', 'who', 'pay', 'days', 'newbie', 'fav'].forEach(function (k) { if (st[k]) q.set(k, st[k]); else q.delete(k); });
      if (sortSel.value) q.set('sort', sortSel.value); else q.delete('sort');
      var qs = q.toString();
      history.replaceState(null, '', location.pathname + (qs ? '?' + qs : '') + location.hash);
    }
  }
  groupBtns.forEach(function (b) {
    b.addEventListener('click', function () { st.g = b.getAttribute('data-filter'); apply(); });
  });
  document.getElementById('zmorebtn').addEventListener('click', function () {
    var hiddenBefore = [];
    list.querySelectorAll('.card').forEach(function (c) { if (c.style.display === 'none') hiddenBefore.push(c); });
    expanded = true;
    apply(false);
    // 続きの最初の求人に移り、読み進めやすくする（キーボードで操作している人のために、そのカードのリンクを選ぶ）。
    var next = hiddenBefore.filter(function (c) { return c.style.display !== 'none'; })[0];
    if (next) { var a = next.querySelector('a'); if (a) a.focus({ preventScroll: true }); }
    track('job_more', { shown: LIMIT });
  });
  chips.forEach(function (b) {
    b.addEventListener('click', function () {
      var k = b.getAttribute('data-k'), v = b.getAttribute('data-v');
      var val = k === 'who' ? v : +v;
      st[k] = String(st[k]) === v ? (k === 'who' ? '' : 0) : val;
      apply();
      track('job_filter', { filter: k, value: String(st[k]) });
    });
  });
  sortSel.addEventListener('change', function () { sortCards(); apply(); track('job_sort', { sort: sortSel.value || 'default' }); });
  function reset() { st = { g: '', who: '', pay: 0, days: 0, newbie: 0, fav: 0 }; apply(); }
  clearBtn.addEventListener('click', reset);
  document.getElementById('znone-reset').addEventListener('click', reset);
  // ♡を押したら、保存した求人の件数・絞り込みを更新する。
  document.addEventListener('click', function (e) {
    if (e.target.closest && e.target.closest('[data-fav]')) setTimeout(function () { apply(false); }, 0);
  });
  // おすすめ（lp/reco.js）で保存したときも同じ。
  document.addEventListener('zaito:favs', function () { apply(false); });
  (function restore() {
    var q = new URLSearchParams(location.search);
    var groups = Array.prototype.map.call(groupBtns, function (b) { return b.getAttribute('data-filter'); });
    if (q.get('g') && groups.indexOf(q.get('g')) !== -1) st.g = q.get('g');
    if (q.get('who') === 'student' || q.get('who') === 'adult') st.who = q.get('who');
    ['pay', 'days', 'newbie', 'fav'].forEach(function (k) {
      var v = +q.get(k);
      if (document.querySelector('.qchips [data-k="' + k + '"][data-v="' + v + '"]')) st[k] = v;
    });
    if (q.get('sort') === 'pay' || q.get('sort') === 'new') { sortSel.value = q.get('sort'); sortCards(); }
    apply(false);
  })();
  // 新着求人のメール登録（トップページ）
  var top = document.getElementById('ztop');
  var profile = document.getElementById('zprofile');
  var stu = profile.querySelector('.stu');
  var done = document.getElementById('zdone');
  var terr = document.getElementById('ztoperr');
  function track(name, params) { if (window.zaitoFb) { window.zaitoFb(name); } if (typeof window.gtag === 'function') { window.gtag('event', name, params); } }
  function showDone(title, text) {
    top.hidden = true; profile.hidden = true; done.hidden = false;
    if (title) document.getElementById('zdonetitle').textContent = title;
    if (text) document.getElementById('zdonetext').textContent = text;
  }
  if (/(?:^|;\s*)zaito_m=1/.test(document.cookie)) {
    showDone('登録済みです', '新しい在宅求人が入ったら、登録したメールアドレスにお知らせします。');
  }
  var q = new URLSearchParams(location.search);
  // Google・LINEで登録して戻ってきたときは、ページの上のまま「登録できました」を画面下に出す（ページ下の登録欄へは移らない）。
  if (q.get('signup') === 'done') {
    try { history.replaceState(null, '', location.pathname + location.search.replace(/([?&])signup=done&?/, '$1').replace(/[?&]$/, '') + location.hash); } catch (x) {}
    var toast = document.createElement('div');
    toast.className = 'sbar';
    toast.setAttribute('role', 'status');
    toast.innerHTML = '<p><b>登録できました</b>新しい在宅求人をメールでお知らせします</p><button type="button" class="btn btn-p">希望の条件を教える</button><button type="button" class="sbar-x" aria-label="閉じる"><span class="ms" aria-hidden="true">close</span></button>';
    document.body.appendChild(toast);
    document.body.classList.add('has-sbar');
    var closeToast = function () { toast.remove(); document.body.classList.remove('has-sbar'); };
    toast.querySelector('.sbar-x').addEventListener('click', closeToast);
    toast.querySelector('.btn').addEventListener('click', function () {
      closeToast();
      top.hidden = true; done.hidden = true; profile.hidden = false;
      document.getElementById('signup').scrollIntoView({ behavior: 'smooth' });
      track('profile_open', { from: 'social' });
    });
  }
  ['utm_source', 'utm_medium', 'utm_campaign'].forEach(function (k) { top.elements[k].value = q.get(k) || ''; });
  top.elements.referrer.value = document.referrer || '';
  var mini = document.getElementById('zmini');
  var bar = document.getElementById('zbar');
  // ヘッダーの「無料登録」は、ページ下の登録欄へ移ってメールアドレスの入力欄を選ぶ。
  document.querySelectorAll('.hd .js-signup').forEach(function (a) {
    a.addEventListener('click', function (e) {
      e.preventDefault();
      document.getElementById('signup').scrollIntoView({ behavior: 'smooth' });
      setTimeout(function () { if (!top.hidden) top.elements.email.focus({ preventScroll: true }); }, 500);
      track('signup_header_click', {});
    });
  });
  var isEmail = function (v) { return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v); };
  // 下の登録欄・求人3件目のあとの登録欄のどちらからでも、同じフォーム(#ztop)で送る。
  function register(btn, err, method, after) {
    btn.disabled = true;
    // form.action は name="action" の入力欄を指してしまうため、属性から読む。
    fetch(top.getAttribute('action'), { method: 'POST', body: new FormData(top), credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
      .then(function (r) { return r.json(); })
      .then(function (res) {
        if (!res || !res.success) { throw new Error(res && res.data && res.data.message ? res.data.message : ''); }
        if (bar) { bar.hidden = true; }
        if (!res.data.created) { showDone('登録済みです', 'このメールアドレスはすでに登録されています。新しい在宅求人が入ったらお知らせします。'); after(false); return; }
        track('sign_up', { method: method });
        top.hidden = true; profile.hidden = false;
        after(true);
      })
      .catch(function (x) {
        btn.disabled = false;
        err.textContent = x.message || '登録できませんでした。時間をおいてもう一度お試しください';
      });
  }
  top.addEventListener('submit', function (e) {
    e.preventDefault();
    terr.textContent = '';
    if (!isEmail(top.elements.email.value.trim())) { terr.textContent = '正しいメールアドレスを入力してください'; return; }
    register(top.querySelector('button[type=submit]'), terr, 'top', function () {});
  });
  if (mini) {
    var mform = document.getElementById('zminiform');
    var merr = document.getElementById('zminierr');
    mform.addEventListener('submit', function (e) {
      e.preventDefault();
      merr.textContent = '';
      var v = document.getElementById('zminiemail').value.trim();
      if (!isEmail(v)) { merr.textContent = '正しいメールアドレスを入力してください'; return; }
      top.elements.email.value = v;
      register(mform.querySelector('button'), merr, 'inline', function (created) {
        // 一覧を読んでいる途中なので、ページ下の登録欄へは自動で移らない（長い距離を一気にスクロールしてしまうため）。
        // 希望条件の質問は、押した人だけ下の登録欄へ案内する。
        mini.innerHTML = '<div class="mc-copy"><span class="ms" aria-hidden="true">check_circle</span><p><b>' + (created ? '登録できました' : '登録済みです') + '</b>' + (created ? '確認メールのリンクを押すと、新着のお知らせが届くようになります。' : '新しい完全在宅の求人が入ったら、メールでお知らせします。') + (created ? '<br><button type="button" class="mc-more">希望の条件を教える（任意）</button>' : '') + '</p></div>';
        var more = mini.querySelector('.mc-more');
        if (more) {
          more.addEventListener('click', function () {
            document.getElementById('signup').scrollIntoView({ behavior: 'smooth' });
            track('profile_open', { from: 'inline' });
          });
        }
      });
    });
  }
  // 広告から来た未登録の人には、画面の下に登録への帯を出す（登録欄が見えている間と、閉じたあとは出さない）。
  var member = /(?:^|;\s*)zaito_m=1/.test(document.cookie);
  var fromAd = q.get('utm_medium') === 'paid';
  try { if (fromAd) { sessionStorage.setItem('zaito_ad', '1'); } fromAd = fromAd || sessionStorage.getItem('zaito_ad') === '1'; } catch (x) {}
  if (member && mini) { mini.remove(); mini = null; }
  var closed = false;
  try { closed = sessionStorage.getItem('zaito_bar_x') === '1'; } catch (x) {}
  // 画面下の登録ボタンは、未登録の人全員に出す（スマホでは登録欄がページのかなり下にあり、たどり着く人が少ないため）。
  if (bar && !member && !closed) {
    var band = document.getElementById('signup');
    var inView = false;
    var sync = function () { bar.hidden = inView || window.scrollY < 300; document.body.classList.toggle('has-sbar', !bar.hidden); };
    if ('IntersectionObserver' in window) {
      new IntersectionObserver(function (es) { inView = es[0].isIntersecting; sync(); }).observe(band);
    }
    window.addEventListener('scroll', sync, { passive: true });
    sync();
    document.getElementById('zbargo').addEventListener('click', function (e) {
      e.preventDefault();
      band.scrollIntoView({ behavior: 'smooth' });
      setTimeout(function () { top.elements.email.focus({ preventScroll: true }); }, 500);
      track('signup_bar_click', {});
    });
    document.getElementById('zbarx').addEventListener('click', function () {
      bar.hidden = true; document.body.classList.remove('has-sbar');
      window.removeEventListener('scroll', sync);
      try { sessionStorage.setItem('zaito_bar_x', '1'); } catch (x) {}
    });
  }
  profile.addEventListener('change', function (e) {
    if (e.target.name === 'role') { stu.hidden = !/生$/.test(e.target.value); }
  });
  profile.addEventListener('submit', function (e) {
    e.preventDefault();
    profile.querySelector('button[type=submit]').disabled = true;
    fetch(profile.getAttribute('action'), { method: 'POST', body: new FormData(profile), credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
      .then(function () { track('profile_complete', { from: 'top' }); }, function () {})
      .then(function () { showDone(); });
  });
  document.getElementById('zskip').addEventListener('click', function () { showDone(); });
})();
</script>
</body>
</html>
