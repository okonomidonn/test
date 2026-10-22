<?php
/**
 * まとめ求人（/remote/）の一覧・詳細ページで共通の <head>・ヘッダー・フッター。
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! function_exists( 'zaito_remote_group' ) ) {
    /**
     * 職種を、絞り込み・色分けに使う大きな分類にまとめる。
     * 戻り値は array( 分類名, アイコン, 色 )。色は remote.css の .t-a〜.t-e。
     */
    function zaito_remote_group( $category ) {
        $groups = array(
            array( 'SNS・マーケ', array( 'SNS', 'マーケ' ), 'campaign', 'a' ),
            array( '教育', array( '教育', '学習' ), 'school', 'c' ),
            array( 'ライティング・編集', array( 'ライティング', '編集', 'ライター' ), 'edit_note', 'd' ),
            array( 'エンジニア', array( 'エンジニア' ), 'code', 'b' ),
            array( 'デザイン・動画', array( 'デザイン', '動画' ), 'palette', 'f' ),
            array( '事務・サポート', array( '事務', 'アシスタント', '秘書', 'サポート' ), 'support_agent', 'e' ),
        );
        foreach ( $groups as $g ) {
            foreach ( $g[1] as $key ) {
                if ( false !== strpos( $category, $key ) ) {
                    return array( $g[0], $g[2], $g[3] );
                }
            }
        }
        return array( 'その他', 'work', 'e' );
    }

    /**
     * 「時給1,500〜2,500円」を、単位・金額・残りに分けて金額を大きく見せる。分けられなければ null。
     */
    function zaito_remote_pay_parts( $pay ) {
        if ( preg_match( '/^(時給|日給|月給)([\d,]+(?:〜[\d,]+)?)(円.*)$/u', $pay, $m ) ) {
            return array( $m[1], $m[2], $m[3] );
        }
        return null;
    }
}

if ( ! function_exists( 'zaito_remote_job_posting' ) ) {
    /**
     * Googleの求人枠（Googleしごと検索）向けの JobPosting 構造化データ。
     * 募集が終わった求人には出さない。validThrough は運営の確認日から30日（2週間ごとに確認し直す前提）。
     */
    function zaito_remote_job_posting( $j ) {
        if ( ! empty( $j['closed'] ) ) {
            return null;
        }
        $text  = $j['title'] . $j['summary'] . $j['target'] . $j['pay'] . ( isset( $j['caution'] ) ? $j['caution'] : '' );
        $types = array();
        if ( false !== strpos( $text, '業務委託' ) || false !== strpos( $text, '成果報酬' ) ) {
            $types[] = 'CONTRACTOR';
        }
        if ( false !== strpos( $text, 'インターン' ) ) {
            $types[] = 'INTERN';
        }
        if ( ! $types ) {
            $types[] = 'PART_TIME';
        }
        $rows = array(
            '仕事内容' => $j['summary'],
            '在宅の条件' => $j['remote'],
            '報酬'     => $j['pay'] ? $j['pay'] : '募集ページで確認してください',
            '稼働条件' => $j['hours'] ? $j['hours'] : '募集ページで確認してください',
            '対象'     => $j['target'],
        );
        if ( ! empty( $j['caution'] ) ) {
            $rows['注意'] = $j['caution'];
        }
        $desc = '';
        foreach ( $rows as $k => $v ) {
            $desc .= '<p><strong>' . esc_html( $k ) . '</strong><br>' . esc_html( $v ) . '</p>';
        }
        $desc .= '<p>応募と選考は、' . esc_html( $j['company'] ) . 'の募集ページで行います。在宅の条件はzaito運営が' . esc_html( date_i18n( 'Y年n月j日', strtotime( $j['checked'] ) ) ) . 'に確認しました。</p>';
        $data = array(
            '@type'              => 'JobPosting',
            'title'              => $j['title'],
            'description'        => $desc,
            // 企業が最初に公開した日が分からない求人が多いため、zaitoに最初に載せた日を使う（確認し直しても変えない）。
            'datePosted'         => ! empty( $j['listed'] ) ? $j['listed'] : $j['checked'],
            'validThrough'       => gmdate( 'Y-m-d', strtotime( $j['checked'] . ' +30 days' ) ) . 'T23:59:59+09:00',
            'employmentType'     => $types,
            'hiringOrganization' => array( '@type' => 'Organization', 'name' => $j['company'] ),
            'jobLocationType'    => 'TELECOMMUTE',
            'applicantLocationRequirements' => array( '@type' => 'Country', 'name' => 'JP' ),
            'directApply'        => false,
            'url'                => zaito_remote_url( $j['slug'] ),
        );
        // 「時給1,300〜1,500円」の形のときだけ、金額をGoogleに渡す（成果報酬などは渡さない）。
        if ( preg_match( '/^時給([\d,]+)(?:〜([\d,]+))?円/u', $j['pay'], $m ) ) {
            // 上限がある「1,300〜1,500円」は範囲で、上限のない「1,300円〜」は金額1つで渡す（minValue だけだと maxValue がないと警告されるため）。
            $min   = (int) str_replace( ',', '', $m[1] );
            $value = array( '@type' => 'QuantitativeValue', 'unitText' => 'HOUR' );
            if ( ! empty( $m[2] ) ) {
                $value['minValue'] = $min;
                $value['maxValue'] = (int) str_replace( ',', '', $m[2] );
            } else {
                $value['value'] = $min;
            }
            $data['baseSalary'] = array( '@type' => 'MonetaryAmount', 'currency' => 'JPY', 'value' => $value );
        }
        return $data;
    }
}

if ( ! function_exists( 'zaito_remote_card_attrs' ) ) {
    /**
     * 一覧の絞り込み・並び替えに使う data 属性（時給の下限・学生OK・社会人OK・週の最少日数・未経験OK・掲載日）。
     */
    function zaito_remote_card_attrs( $j ) {
        $student = (bool) preg_match( '/学生|大学|全学年/u', $j['target'] );
        $adult   = ! $student || (bool) preg_match( '/社会人|主婦|既卒|副業|フリーランス/u', $j['target'] );
        $days    = preg_match( '/週(\d)(?:日|〜)/u', $j['hours'], $m ) ? (int) $m[1] : 0;
        $newbie  = function_exists( 'zaito_remote_has_tag' ) && zaito_remote_has_tag( $j, 'mikeiken' ) || (bool) preg_match( '/未経験|経験不問|経験は不問/u', $j['target'] );
        return sprintf(
            ' data-pay="%d" data-student="%d" data-adult="%d" data-days="%d" data-newbie="%d" data-listed="%d"',
            function_exists( 'zaito_remote_hourly_min' ) ? zaito_remote_hourly_min( $j['pay'] ) : 0,
            $student ? 1 : 0,
            $adult ? 1 : 0,
            $days,
            $newbie ? 1 : 0,
            (int) str_replace( '-', '', ! empty( $j['listed'] ) ? $j['listed'] : $j['checked'] )
        );
    }
}

if ( ! function_exists( 'zaito_u022_heart' ) ) {
    /**
     * 保存（しおり）ボタンの中身。押すとしおりがはねて、輪と小さな点がはじける（lp/u022.css・u022.js）。
     */
    function zaito_u022_heart() {
        static $n = 0;
        $n++;
        $dots = array(
            array( 0, '#3D5AFE', '#FFB547' ), array( 51, '#8B6CFF', '#5B8CFF' ), array( 103, '#FFB547', '#22C3A6' ),
            array( 154, '#3D5AFE', '#8B6CFF' ), array( 206, '#22C3A6', '#FFB547' ), array( 257, '#5B8CFF', '#3D8BFF' ),
            array( 309, '#3D8BFF', '#3D5AFE' ),
        );
        $d   = 'M15 6h22a4 4 0 0 1 4 4v36L26 36.5 11 46V10a4 4 0 0 1 4-4z';
        $out = '<span class="u022-fx" aria-hidden="true"><span class="u022-ring"></span>';
        foreach ( $dots as $dot ) {
            $out .= '<span class="u022-dot" style="--a:' . (int) $dot[0] . 'deg;--c:' . $dot[1] . ';--c2:' . $dot[2] . '"><i></i><i></i></span>';
        }
        $out .= '<span class="u022-heart"><svg viewBox="0 0 52 52"><defs><linearGradient id="u022-g' . $n . '" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#6F84FF"/><stop offset="1" stop-color="#3D5AFE"/></linearGradient></defs>'
            . '<path class="u022-f" fill="url(#u022-g' . $n . ')" d="' . $d . '"/><path class="u022-o" d="' . $d . '"/></svg></span></span>';
        return $out;
    }
}

if ( ! function_exists( 'zaito_share_box' ) ) {
    /**
     * 「友達に教える」。$text は共有するときの一言、$url は共有するページ。
     * どこから共有されて来たか分かるよう、URLに utm_source（line / x / copy / native）を付ける。
     */
    function zaito_share_box( $text, $url, $label = '友達に教える' ) {
        $u = function ( $src ) use ( $url ) {
            return add_query_arg( array( 'utm_source' => $src, 'utm_medium' => 'share' ), $url );
        };
        ?>
<div class="share">
  <span class="share-lb"><?php echo esc_html( $label ); ?></span>
  <div class="share-row">
    <a class="sh" data-how="line" href="<?php echo esc_url( 'https://social-plugins.line.me/lineit/share?url=' . rawurlencode( $u( 'line' ) ) . '&text=' . rawurlencode( $text ) ); ?>" target="_blank" rel="noopener"><span class="sh-i line" aria-hidden="true"><svg viewBox="0 0 24 24"><path fill="#fff" d="M12 3.5c-5 0-9 3.2-9 7.2 0 3.6 3.2 6.6 7.5 7.1.3.1.7.2.8.5.1.2 0 .6 0 .8l-.1.8c0 .2-.2.9.8.5s5.4-3.2 7.4-5.4c1.4-1.5 2-3 2-4.3 0-4-4-7.2-9-7.2z"/></svg></span><span class="sh-t">LINE</span></a>
    <a class="sh" data-how="x" href="<?php echo esc_url( 'https://x.com/intent/post?text=' . rawurlencode( $text ) . '&url=' . rawurlencode( $u( 'x' ) ) ); ?>" target="_blank" rel="noopener"><span class="sh-i x" aria-hidden="true"><svg viewBox="0 0 24 24"><path fill="#fff" d="M17.8 3.5h3l-6.6 7.5 7.8 10.3h-6.1l-4.8-6.2-5.5 6.2h-3l7-8L1.9 3.5h6.2l4.3 5.7 5.4-5.7zm-1 16h1.7L7.3 5.2H5.5l11.3 14.3z"/></svg></span><span class="sh-t">X</span></a>
    <button type="button" class="sh" data-copy="<?php echo esc_url( $u( 'copy' ) ); ?>" hidden><span class="sh-i" aria-hidden="true"><span class="ms">link</span></span><span class="sh-t">リンクをコピー</span></button>
    <button type="button" class="sh" data-native="<?php echo esc_url( $u( 'native' ) ); ?>" data-title="zaito" data-text="<?php echo esc_attr( $text ); ?>" hidden><span class="sh-i" aria-hidden="true"><span class="ms">ios_share</span></span><span class="sh-t">共有</span></button>
  </div>
</div>
<script src="<?php echo esc_url( zaito_lp_asset( 'share.js' ) ); ?>" defer></script>
        <?php
    }
}

if ( ! function_exists( 'zaito_remote_profile_fields' ) ) {
    /**
     * 登録直後に聞く任意の希望条件（立場・学年・学校名・やってみたい仕事・働ける日数・希望の時給）の入力欄。
 * 新着メールは、この答えに合う求人だけを送る（inc/newsletter.php）。
     * 求人詳細とトップページの登録フォームで共通。学年・学校名は学生を選んだときだけJSで表示する。
     */
    function zaito_remote_profile_fields() {
        $opts = zaito_remote_profile_options();
        // 質問ごとの .pq は、登録直後のフォームで1問ずつ表示するときの区切り（lp/u063.js）。マイページではそのまま並べる。
        ?>
    <div class="pq" data-step="role" data-hint="あなたが応募できる求人だけをお送りします">
    <fieldset><legend>現在の職業</legend><div class="chips">
      <?php foreach ( $opts['role'] as $o ) : ?>
        <label><input type="radio" name="role" value="<?php echo esc_attr( $o ); ?>"><span><?php echo esc_html( $o ); ?></span></label>
      <?php endforeach; ?>
    </div></fieldset>
    </div>
    <div class="stu pq" data-step="stu" data-optional="1" hidden>
      <fieldset><legend>学年</legend><div class="chips">
        <?php foreach ( $opts['grade'] as $o ) : ?>
          <label><input type="radio" name="grade" value="<?php echo esc_attr( $o ); ?>"><span><?php echo esc_html( $o ); ?></span></label>
        <?php endforeach; ?>
      </div></fieldset>
      <label class="lb" for="zschool">学校名</label>
      <input type="text" id="zschool" name="school" maxlength="80" autocomplete="organization" placeholder="例：〇〇大学" aria-describedby="zschool-note" data-schools="<?php echo esc_url( zaito_lp_asset( 'schools.json' ) ); ?>">
      <p class="school-note" id="zschool-note">あなたの学校向けの求人が届きやすくなります</p>
      <script src="<?php echo esc_url( zaito_lp_asset( 'school-suggest.js' ) ); ?>" defer></script>
    </div>
    <div class="pq" data-step="interests" data-hint="いくつ選んでもかまいません">
    <fieldset><legend>やってみたい仕事（いくつでも）</legend><div class="chips">
      <?php foreach ( $opts['interests'] as $o ) : ?>
        <label><input type="checkbox" name="interests[]" value="<?php echo esc_attr( $o ); ?>"><span><?php echo esc_html( $o ); ?></span></label>
      <?php endforeach; ?>
    </div></fieldset>
    </div>
    <div class="pq" data-step="days" data-hint="働ける日数に合う求人だけをお送りします">
    <fieldset><legend>週に働けそうな日数</legend><div class="chips">
      <?php foreach ( $opts['days'] as $o ) : ?>
        <label><input type="radio" name="days" value="<?php echo esc_attr( $o ); ?>"><span><?php echo esc_html( $o ); ?></span></label>
      <?php endforeach; ?>
    </div></fieldset>
    </div>
    <div class="pq" data-step="min_pay">
    <fieldset><legend>希望の時給</legend><div class="chips">
      <?php foreach ( $opts['min_pay'] as $o ) : ?>
        <label><input type="radio" name="min_pay" value="<?php echo esc_attr( $o ); ?>"><span><?php echo esc_html( $o ); ?></span></label>
      <?php endforeach; ?>
    </div></fieldset>
    </div>
        <?php
    }
}

if ( ! function_exists( 'zaito_remote_head' ) ) {
    function zaito_remote_head( $title, $description, $canonical, $json_ld = null ) {
        ?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title><?php echo esc_html( $title ); ?></title>
<meta name="description" content="<?php echo esc_attr( $description ); ?>">
<link rel="canonical" href="<?php echo esc_url( $canonical ); ?>">
<meta property="og:site_name" content="zaito">
<meta property="og:type" content="website">
<meta property="og:locale" content="ja_JP">
<meta property="og:title" content="<?php echo esc_attr( $title ); ?>">
<meta property="og:description" content="<?php echo esc_attr( $description ); ?>">
<meta property="og:url" content="<?php echo esc_url( $canonical ); ?>">
<meta property="og:image" content="<?php echo esc_url( get_template_directory_uri() . '/lp/ogp.png' ); ?>">
<meta name="twitter:card" content="summary_large_image">
<link rel="icon" href="<?php echo esc_url( get_template_directory_uri() . '/lp/favicon.svg' ); ?>" type="image/svg+xml">
<link rel="icon" href="<?php echo esc_url( get_template_directory_uri() . '/lp/favicon-32.png' ); ?>" sizes="32x32" type="image/png">
<link rel="apple-touch-icon" href="<?php echo esc_url( get_template_directory_uri() . '/lp/apple-touch-icon.png' ); ?>">
<meta name="theme-color" content="#ffffff">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;500;700;900&amp;family=Outfit:wght@500;600;700&amp;display=swap" rel="stylesheet">
<?php // アイコンは使うものだけを読み込む（全部だと約540KB、使う分だけで約8KB）。新しいアイコンを使うときは、ここに名前を足す（アルファベット順）。 ?>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0..1,0&amp;icon_names=add,apps,arrow_back,arrow_forward,block,bookmark,call,campaign,check_circle,close,code,description,edit_document,edit_note,emoji_people,fact_check,favorite,home,home_work,info,insights,ios_share,lightbulb,link,mail,mark_email_read,mark_email_unread,notifications,open_in_new,palette,payments,person,schedule,school,search,support_agent,swap_vert,timelapse,verified,visibility_off,warning,work,work_history&amp;display=block" rel="stylesheet">
<link rel="stylesheet" href="<?php echo esc_url( zaito_lp_asset( 'tokens.css' ) ); ?>">
<link rel="stylesheet" href="<?php echo esc_url( zaito_lp_asset( 'remote.css' ) ); ?>">
<?php if ( $json_ld ) : ?>
<script type="application/ld+json"><?php echo wp_json_encode( $json_ld, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ); ?></script>
<?php endif; ?>
<?php echo zaito_ga4_snippet(); // 測定IDは形式チェック済み ?>
</head>
        <?php
    }
}

if ( ! function_exists( 'zaito_remote_header' ) ) {
    /**
     * $for: 'user'（求人を探す人向け。右上は「無料登録」）か 'company'（企業向けページ。右上は「無料で求人掲載」）。
     * 「無料登録」は登録済みのブラウザでは出さない（lp/fav.js が zaito_m Cookie を見て消す）。
     */
    function zaito_remote_header( $for = 'user' ) {
        ?>
<header class="hd"><div class="wrap">
  <a class="logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="zaito トップへ">za<span>i</span>to</a>
  <nav aria-label="メインナビゲーション">
    <a class="tx" href="<?php echo esc_url( zaito_remote_url() ); ?>">求人を探す</a>
    <a class="tx my" href="<?php echo esc_url( home_url( '/mypage/' ) ); ?>" aria-label="マイページ（保存した求人）"><span class="ms" aria-hidden="true">person</span><span class="lb">マイページ</span></a>
    <a class="tx" href="<?php echo esc_url( home_url( '/for-companies/' ) ); ?>">企業の方</a>
    <?php if ( 'company' === $for ) : ?>
      <a class="btn btn-p" href="<?php echo esc_url( home_url( '/for-companies/#contact' ) ); ?>">無料で求人掲載</a>
    <?php else : ?>
      <a class="btn btn-p js-signup" href="<?php echo esc_url( home_url( '/#signup' ) ); ?>">無料登録</a>
    <?php endif; ?>
  </nav>
</div></header>
        <?php
    }
}

if ( ! function_exists( 'zaito_remote_footer' ) ) {
    function zaito_remote_footer() {
        ?>
<footer class="ft"><div class="wrap">
  <div class="ft-top"><div class="ft-brand"><p>出社なしの仕事だけを<br>集めた求人サイト</p><?php echo zaito_sns_links_html(); // SVGと固定URLのみ ?></div>
    <nav aria-label="フッターナビゲーション"><a href="<?php echo esc_url( zaito_remote_url() ); ?>">求人を探す</a><a href="<?php echo esc_url( home_url( '/mypage/' ) ); ?>">マイページ</a><a href="<?php echo esc_url( home_url( '/kijun/' ) ); ?>">掲載基準</a><a href="<?php echo esc_url( home_url( '/faq/' ) ); ?>">よくある質問</a><a href="<?php echo esc_url( home_url( '/zaitaku/gakusei/' ) ); ?>">大学生の在宅バイト</a><a href="<?php echo esc_url( home_url( '/guide/' ) ); ?>">在宅バイトの記事</a><a href="<?php echo esc_url( home_url( '/for-companies/' ) ); ?>">企業の方へ</a><a href="<?php echo esc_url( home_url( '/for-companies/#contact' ) ); ?>">掲載・削除のご依頼</a><a href="<?php echo esc_url( home_url( '/terms/' ) ); ?>">利用規約</a><a href="<?php echo esc_url( home_url( '/privacy/' ) ); ?>">プライバシーポリシー</a></nav></div>
  <div class="ft-b"><span>運営：zaito 運営事務局</span><small>Copyright © zaito</small></div>
  <div class="logo ft-big" aria-hidden="true">za<span>i</span>to</div>
</div></footer>
<script src="<?php echo esc_url( zaito_lp_asset( 'fav.js' ) ); ?>" data-ajax="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" data-my="<?php echo esc_url( home_url( '/mypage/' ) ); ?>"></script>
        <?php
    }
}
