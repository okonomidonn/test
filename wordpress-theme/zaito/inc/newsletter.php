<?php
/**
 * 新着求人メールの一斉配信（管理画面「求人 > 新着メール配信」）。
 *
 * - 送り先: 「興味あり登録」(zaito_interest) のメールアドレス全員。配信停止した人と、メール確認がまだの人は除く。
 * - 希望条件に合わせる: 選んだ求人のうち、その人の希望（立場・やってみたい仕事・働ける日数・時給の下限）に
 *   合うものだけを一人ずつ選んで送る。合う求人が1件もない人には送らない。
 * - 計測: 求人へのリンクに配信ID・登録ID・署名を付け、配信ごとに「メールから戻った人」
 *   「応募ページへ進んだ人」「配信停止した人」を数える（zaito_nl_stats）。
 * - 中身: 運営が選んだまとめ求人（zaito_remote_jobs()）の一覧。リンクはzaitoの求人ページ
 *   （応募ボタンの登録・クリック記録を通るように）に、流入元が分かるUTMを付けて貼る。
 * - 送信: 管理画面を開いたまま、JavaScriptが20件ずつ送る（サーバーの実行時間の上限に
 *   かからないようにするため）。画面を閉じると途中で止まり、もう一度開くと続きから送れる。
 * - 配信停止: 各メールに本人専用の停止リンクを入れる。リンクを開くと確認ボタンが出て、
 *   押すと停止する（メールソフトがリンクを先読みしただけで停止されないようにするため）。
 *   メールへの「配信停止」の返信は、管理画面の入力欄から手動で停止にする。
 */

define( 'ZAITO_NL_BATCH', 20 );

/* ---------- 送り先 ---------- */

function zaito_nl_recipients() {
    $ids  = get_posts( array(
        'post_type'      => 'zaito_interest',
        'post_status'    => 'any',
        'posts_per_page' => -1,
        'fields'         => 'ids',
        'orderby'        => 'ID',
        'order'          => 'ASC',
    ) );
    $seen = array();
    $out  = array();
    foreach ( $ids as $id ) {
        if ( get_post_meta( $id, 'unsubscribed', true ) || ! zaito_interest_verified( $id ) ) {
            continue;
        }
        $email = strtolower( trim( (string) get_post_meta( $id, 'email', true ) ) );
        if ( ! is_email( $email ) || isset( $seen[ $email ] ) ) {
            continue;
        }
        $seen[ $email ] = true;
        $out[]          = (int) $id;
    }
    return $out;
}

/* ---------- 配信停止 ---------- */

function zaito_nl_unsub_sig( $id ) {
    return hash_hmac( 'sha256', 'zaito_unsub|' . (int) $id, wp_salt( 'auth' ) );
}

function zaito_nl_unsub_url( $id, $campaign = '' ) {
    $args = array( 'zaito_unsub' => (int) $id, 'sig' => zaito_nl_unsub_sig( $id ) );
    if ( '' !== $campaign ) {
        $args['c'] = $campaign;
    }
    return add_query_arg( $args, home_url( '/' ) );
}

function zaito_nl_mark_unsubscribed( $id ) {
    update_post_meta( $id, 'unsubscribed', current_time( 'Y-m-d H:i' ) );
}

/**
 * 停止リンク。GETでは確認画面だけを出し、ボタン（POST）で停止する。
 * メールソフトの「ワンクリック停止」（List-Unsubscribe-Post）もPOSTで届く。
 */
function zaito_nl_handle_unsubscribe() {
    if ( ! isset( $_GET['zaito_unsub'], $_GET['sig'] ) ) {
        return;
    }
    $id  = (int) $_GET['zaito_unsub'];
    $sig = (string) wp_unslash( $_GET['sig'] );
    $ok  = $id && 'zaito_interest' === get_post_type( $id ) && hash_equals( zaito_nl_unsub_sig( $id ), $sig );

    $done = false;
    if ( $ok && 'POST' === ( isset( $_SERVER['REQUEST_METHOD'] ) ? $_SERVER['REQUEST_METHOD'] : '' ) ) {
        if ( ! get_post_meta( $id, 'unsubscribed', true ) && isset( $_GET['c'] ) ) {
            zaito_nl_stat( sanitize_key( wp_unslash( $_GET['c'] ) ), 'unsub', $id );
        }
        zaito_nl_mark_unsubscribed( $id );
        $done = true;
    }
    nocache_headers();
    status_header( $ok ? 200 : 400 );
    header( 'Content-Type: text/html; charset=UTF-8' );
    header( 'X-Robots-Tag: noindex, nofollow' );
    $email = $ok ? (string) get_post_meta( $id, 'email', true ) : '';
    ?>
<!DOCTYPE html><html lang="ja"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="robots" content="noindex, nofollow"><title>配信停止 | zaito</title>
<style>body{margin:0;font-family:'Noto Sans JP',system-ui,sans-serif;background:#F6F7FA;color:#0B1530;line-height:1.8}main{max-width:520px;margin:12vh auto;padding:32px 24px;background:#fff;border-radius:20px;border:1px solid #E6E9F0}h1{margin:0 0 12px;font-size:22px}p{margin:0 0 16px;color:#3A4563}button{height:52px;padding:0 24px;border:0;border-radius:12px;background:#3D5AFE;color:#fff;font:inherit;font-weight:700;cursor:pointer}a{color:#3D5AFE}</style></head>
<body><main>
<?php if ( ! $ok ) : ?>
  <h1>リンクが正しくありません</h1>
  <p>お手数ですが、届いたメールに「配信停止」と返信してください。</p>
<?php elseif ( $done || get_post_meta( $id, 'unsubscribed', true ) ) : ?>
  <h1>配信を停止しました</h1>
  <p><?php echo esc_html( $email ); ?> への新着求人メールの配信を停止しました。</p>
  <p><a href="<?php echo esc_url( home_url( '/' ) ); ?>">zaitoのトップへ</a></p>
<?php else : ?>
  <h1>新着求人メールの配信を停止しますか？</h1>
  <p><?php echo esc_html( $email ); ?> への配信を停止します。</p>
  <form method="post"><button type="submit">配信を停止する</button></form>
<?php endif; ?>
</main></body></html>
    <?php
    exit;
}
add_action( 'init', 'zaito_nl_handle_unsubscribe', 1 );

/* ---------- 希望条件との照らし合わせ ---------- */

/**
 * 「やってみたい仕事」の選択肢を、求人の分類（zaito_remote_group）に対応させる。
 */
function zaito_nl_interest_groups() {
    return array(
        'SNS運用'            => 'SNS・マーケ',
        'Webマーケティング'  => 'SNS・マーケ',
        'ライティング・編集' => 'ライティング・編集',
        '動画編集'           => 'デザイン・動画',
        'デザイン'           => 'デザイン・動画',
        'エンジニア'         => 'エンジニア',
        '事務・アシスタント' => '事務・サポート',
        'カスタマーサポート' => '事務・サポート',
        'リサーチ・データ入力' => '事務・サポート',
        '教育・学習サポート' => '教育',
    );
}

/**
 * 登録者の希望条件。答えていない項目は空（＝条件にしない）。
 */
function zaito_nl_member_prefs( $id ) {
    $map       = zaito_nl_interest_groups();
    $interests = get_post_meta( $id, 'interests', true );
    $groups    = array();
    foreach ( is_array( $interests ) ? $interests : array() as $i ) {
        if ( isset( $map[ $i ] ) ) {
            $groups[ $map[ $i ] ] = true;
        }
    }
    $days = (string) get_post_meta( $id, 'days', true );
    return array(
        'role'     => (string) get_post_meta( $id, 'role', true ),
        'groups'   => array_keys( $groups ),
        'days_max' => preg_match( '/週(\d)〜(\d)日|週(\d)日/u', $days, $m ) ? (int) ( ! empty( $m[2] ) ? $m[2] : $m[3] ) : 0,
        'min_pay'  => preg_match( '/時給([\d,]+)円以上/u', (string) get_post_meta( $id, 'min_pay', true ), $m2 ) ? (int) str_replace( ',', '', $m2[1] ) : 0,
    );
}

/**
 * 求人が希望条件に合うか。分からない項目（求人に日数が書かれていないなど）は合うものとして扱う。
 */
function zaito_nl_job_matches( $prefs, $j ) {
    $student = (bool) preg_match( '/生$/u', $prefs['role'] );
    if ( $student && ! preg_match( '/学生|大学|全学年/u', $j['target'] ) ) {
        return false;
    }
    if ( $prefs['groups'] ) {
        $g = zaito_remote_group( $j['category'] );
        if ( ! in_array( $g[0], $prefs['groups'], true ) ) {
            return false;
        }
    }
    if ( $prefs['min_pay'] && zaito_remote_hourly_min( $j['pay'] ) < $prefs['min_pay'] ) {
        return false;
    }
    if ( $prefs['days_max'] && preg_match( '/週(\d)/u', $j['hours'], $m ) && (int) $m[1] > $prefs['days_max'] ) {
        return false;
    }
    return true;
}

/* ---------- 配信ごとの計測 ---------- */

function zaito_nl_stat( $campaign, $kind, $member ) {
    if ( '' === $campaign || ! $member ) {
        return;
    }
    $stats = get_option( 'zaito_nl_stats', array() );
    $stats = is_array( $stats ) ? $stats : array();
    if ( ! isset( $stats[ $campaign ] ) ) {
        $stats[ $campaign ] = array( 'returns' => array(), 'clicks' => array(), 'unsub' => array() );
    }
    if ( 'clicks' === $kind ) {
        $stats[ $campaign ]['clicks'][ $member ] = ( isset( $stats[ $campaign ]['clicks'][ $member ] ) ? $stats[ $campaign ]['clicks'][ $member ] : 0 ) + 1;
    } elseif ( ! isset( $stats[ $campaign ][ $kind ][ $member ] ) ) {
        $stats[ $campaign ][ $kind ][ $member ] = current_time( 'Y-m-d H:i' );
    }
    update_option( 'zaito_nl_stats', array_slice( $stats, -50, null, true ), false );
}

function zaito_nl_link_sig( $campaign, $member ) {
    return substr( hash_hmac( 'sha256', 'zaito_nl|' . $campaign . '|' . (int) $member, wp_salt( 'auth' ) ), 0, 16 );
}

/**
 * メールのリンクから来た人を数える。登録済みの本人なので、登録済みの印（Cookie）も付け直す
 * （別の端末で開いても、応募ページへそのまま進めるように）。この訪問の間に応募ページへ進んだら、その配信の成果として数える。
 */
function zaito_nl_track_return() {
    if ( ! isset( $_GET['nl'], $_GET['m'], $_GET['s'] ) ) {
        return;
    }
    $campaign = sanitize_key( wp_unslash( $_GET['nl'] ) );
    $member   = (int) $_GET['m'];
    $sig      = (string) wp_unslash( $_GET['s'] );
    if ( ! $member || 'zaito_interest' !== get_post_type( $member ) || ! hash_equals( zaito_nl_link_sig( $campaign, $member ), $sig ) ) {
        return;
    }
    zaito_nl_stat( $campaign, 'returns', $member );
    if ( ! zaito_remote_member_id() ) {
        zaito_remote_set_member_cookie( $member );
    }
    setcookie( 'zaito_nl', $campaign . '.' . $member, array( 'expires' => time() + DAY_IN_SECONDS, 'path' => '/', 'secure' => is_ssl(), 'httponly' => true, 'samesite' => 'Lax' ) );
    $_COOKIE['zaito_nl'] = $campaign . '.' . $member;
    // 本人専用のリンクなので、マイページも使えるようにする（inc/mypage.php）。
    do_action( 'zaito_nl_returned', $member );
}
add_action( 'init', 'zaito_nl_track_return', 5 );

/**
 * 応募ページへ進んだとき（zaito_remote_record_click）に呼ばれる。メールから来た訪問なら、その配信に数える。
 */
function zaito_nl_track_click( $slug, $member ) {
    $raw = isset( $_COOKIE['zaito_nl'] ) ? (string) wp_unslash( $_COOKIE['zaito_nl'] ) : '';
    if ( preg_match( '/^([a-z0-9_-]+)\.(\d+)$/', $raw, $m ) && (int) $m[2] === (int) $member ) {
        zaito_nl_stat( $m[1], 'clicks', $member );
    }
}
add_action( 'zaito_remote_click', 'zaito_nl_track_click', 10, 2 );

/* ---------- メールの中身 ---------- */

function zaito_nl_job_url( $slug, $campaign, $member = 0 ) {
    $args = array(
        'utm_source'   => 'newsletter',
        'utm_medium'   => 'email',
        'utm_campaign' => $campaign,
    );
    if ( $member ) {
        $args['nl'] = $campaign;
        $args['m']  = (int) $member;
        $args['s']  = zaito_nl_link_sig( $campaign, $member );
    }
    return add_query_arg( $args, '' === $slug ? home_url( '/' ) : zaito_remote_url( $slug ) );
}

function zaito_nl_mypage_url( $campaign, $member ) {
    return add_query_arg( array( 'nl' => $campaign, 'm' => (int) $member, 's' => zaito_nl_link_sig( $campaign, $member ) ), home_url( '/mypage/' ) );
}

/**
 * 本文。{UNSUB} は送るときに一人ずつの停止リンクに置き換える。
 */
function zaito_nl_build_body( $intro, $slugs, $campaign, $member = 0 ) {
    $host = preg_replace( '/^www\./', '', (string) wp_parse_url( home_url(), PHP_URL_HOST ) );
    $body = trim( $intro ) . "\n\n"
        . "━━━━━━━━━━━━━━━━\n"
        . "新しく載った在宅求人\n"
        . "━━━━━━━━━━━━━━━━\n\n";
    foreach ( $slugs as $slug ) {
        $j = zaito_remote_job( $slug );
        if ( ! $j ) {
            continue;
        }
        $body .= '■ ' . $j['title'] . "\n"
            . '　' . $j['company'] . "\n"
            . '　報酬：' . ( $j['pay'] ? $j['pay'] : '募集ページで確認' ) . "\n"
            . ( $j['hours'] ? '　働き方：' . $j['hours'] . "\n" : '' )
            . ( ! empty( $j['caution'] ) ? '　※' . $j['caution'] . "\n" : '' )
            . '　' . zaito_nl_job_url( $slug, $campaign, $member ) . "\n\n";
    }
    $body .= "すべての在宅求人を見る\n"
        . zaito_nl_job_url( '', $campaign, $member ) . "\n\n"
        . "──────────\n"
        . "このメールは、zaitoに登録したメールアドレスに、登録時に答えていただいた希望に合う求人をお送りしています。\n"
        . ( $member ? "希望条件の変更・保存した求人（マイページ）\n" . zaito_nl_mypage_url( $campaign, $member ) . "\n" : '' )
        . "配信を停止する: {UNSUB}\n"
        . "（このメールに「配信停止」と返信していただいても停止できます）\n\n"
        . "zaito（ザイト）出社なしの仕事だけを集めた求人サイト\n"
        . "運営：zaito 運営事務局\n"
        . home_url( '/' ) . "\n"
        . 'お問い合わせ: info@' . $host . "\n";
    return $body;
}

function zaito_nl_send_one( $to, $subject, $body, $unsub_url ) {
    $host    = preg_replace( '/^www\./', '', (string) wp_parse_url( home_url(), PHP_URL_HOST ) );
    $headers = array(
        'From: zaito <info@' . $host . '>',
        'List-Unsubscribe: <' . $unsub_url . '>',
        'List-Unsubscribe-Post: List-Unsubscribe=One-Click',
    );
    return wp_mail( $to, $subject, str_replace( '{UNSUB}', $unsub_url, $body ), $headers );
}

/* ---------- 管理画面 ---------- */

function zaito_nl_admin_menu() {
    add_submenu_page( 'edit.php?post_type=job_listing', '新着メール配信', '新着メール配信', 'manage_options', 'zaito-newsletter', 'zaito_nl_render_admin' );
}
add_action( 'admin_menu', 'zaito_nl_admin_menu' );

function zaito_nl_default_intro() {
    return "zaitoに登録いただきありがとうございます。\n出社なしで働ける、新しい在宅求人をお知らせします。\n気になる求人は、リンク先の「応募ページへ進む」から応募できます。";
}

function zaito_nl_render_admin() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( 'この画面を表示する権限がありません。' );
    }
    $notice = '';
    if ( isset( $_POST['zaito_nl_unsub_email'] ) ) {
        check_admin_referer( 'zaito_nl_unsub' );
        $email = strtolower( trim( sanitize_email( wp_unslash( $_POST['zaito_nl_unsub_email'] ) ) ) );
        $ids   = $email ? get_posts( array(
            'post_type'      => 'zaito_interest',
            'post_status'    => 'any',
            'posts_per_page' => -1,
            'fields'         => 'ids',
            'meta_query'     => array( array( 'key' => 'email', 'value' => $email ) ),
        ) ) : array();
        foreach ( $ids as $id ) {
            zaito_nl_mark_unsubscribed( $id );
        }
        $notice = $ids ? $email . ' を配信停止にしました。' : $email . ' は登録されていませんでした。';
    }

    $announced  = get_option( 'zaito_nl_announced', array() );
    $announced  = is_array( $announced ) ? $announced : array();
    $jobs       = array_filter( zaito_remote_jobs(), function ( $j ) {
        return empty( $j['closed'] );
    } );
    $recipients = count( zaito_nl_recipients() );
    $unverified = count( array_filter( get_posts( array( 'post_type' => 'zaito_interest', 'post_status' => 'any', 'posts_per_page' => -1, 'fields' => 'ids' ) ), function ( $id ) {
        return ! get_post_meta( $id, 'unsubscribed', true ) && ! zaito_interest_verified( $id );
    } ) );
    $history    = get_option( 'zaito_nl_history', array() );
    $campaign   = get_option( 'zaito_nl_campaign' );
    $pending    = is_array( $campaign ) && $campaign['next'] < count( $campaign['to'] );
    ?>
    <div class="wrap" id="znl">
      <h1>新着メール配信</h1>
      <p>登録している人（配信停止した人を除く）に、新しい在宅求人をまとめたメールを一斉に送ります。送り先は今 <strong><?php echo esc_html( $recipients ); ?>人</strong> です。</p>
      <?php if ( $notice ) : ?><div class="notice notice-success"><p><?php echo esc_html( $notice ); ?></p></div><?php endif; ?>
      <?php if ( $pending ) : ?>
        <div class="notice notice-warning"><p>前回の配信「<?php echo esc_html( $campaign['subject'] ); ?>」が途中で止まっています（<?php echo esc_html( $campaign['next'] ); ?> / <?php echo esc_html( count( $campaign['to'] ) ); ?>件送信済み）。<button type="button" class="button" id="znl-resume">続きを送る</button></p></div>
      <?php endif; ?>

      <form id="znl-form">
        <h2>1. 載せる求人を選ぶ</h2>
        <p class="description">まだメールで知らせていない求人に、最初からチェックを入れています。</p>
        <table class="widefat striped" style="max-width:960px">
          <thead><tr><th style="width:40px"></th><th>求人</th><th style="width:140px">これまでの案内</th></tr></thead>
          <tbody>
          <?php foreach ( $jobs as $j ) : $was = isset( $announced[ $j['slug'] ] ) ? $announced[ $j['slug'] ] : ''; ?>
            <tr>
              <td><input type="checkbox" name="slugs[]" value="<?php echo esc_attr( $j['slug'] ); ?>" <?php checked( '' === $was ); ?>></td>
              <td><strong><?php echo esc_html( $j['title'] ); ?></strong><br><span style="color:#646970"><?php echo esc_html( $j['company'] ); ?>　<?php echo esc_html( $j['pay'] ? $j['pay'] : '' ); ?></span></td>
              <td><?php echo esc_html( $was ? $was . ' に送信' : '未送信' ); ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>

        <p><label><input type="checkbox" name="match" value="1" checked> 一人ずつ、希望条件（立場・やってみたい仕事・働ける日数・時給の下限）に合う求人だけを送る。合う求人がない人には送らない</label><br><span class="description">チェックを入れると、件名は「あなたの希望に合う在宅求人が◯件入りました」に自動で変わります。希望を答えていない人には、選んだ求人をすべて送ります。</span></p>

        <h2>2. 件名と冒頭の文章</h2>
        <p><label>件名<br><input type="text" name="subject" id="znl-subject" class="large-text" style="max-width:960px" value=""></label></p>
        <p><label>冒頭の文章<br><textarea name="intro" rows="4" class="large-text" style="max-width:960px"><?php echo esc_textarea( zaito_nl_default_intro() ); ?></textarea></label></p>

        <h2>3. 確認して送る</h2>
        <p>
          <button type="button" class="button" id="znl-preview">本文を確認する</button>
          <input type="email" name="test_to" value="<?php echo esc_attr( get_option( 'admin_email' ) ); ?>" style="width:280px">
          <button type="button" class="button" id="znl-test">このアドレスにテスト送信</button>
        </p>
        <pre id="znl-body" style="display:none;max-width:960px;white-space:pre-wrap;background:#fff;border:1px solid #c3c4c7;padding:16px;font-size:13px"></pre>
        <p><button type="button" class="button button-primary button-large" id="znl-send">登録者 <?php echo esc_html( $recipients ); ?>人に送信する</button><?php if ( $unverified ) : ?>　<span style="color:#646970">メール確認がまだの <?php echo esc_html( $unverified ); ?>人には送りません（登録時の確認メールのリンクを押すと、送り先に入ります）</span><?php endif; ?></p>
        <p id="znl-status" style="font-size:14px;font-weight:600"></p>
      </form>

      <h2>配信の記録</h2>
      <?php if ( $history ) : ?>
        <table class="widefat striped" style="max-width:960px">
          <thead><tr><th style="width:140px">日時</th><th>件名</th><th style="width:60px">求人</th><th style="width:70px">送信</th><th style="width:90px">合う求人なし</th><th style="width:90px">メールから戻った</th><th style="width:90px">応募ページへ進んだ</th><th style="width:70px">配信停止</th></tr></thead>
          <tbody>
          <?php
          $stats = get_option( 'zaito_nl_stats', array() );
          foreach ( array_reverse( $history ) as $h ) :
              $st   = ! empty( $h['id'] ) && isset( $stats[ $h['id'] ] ) ? $stats[ $h['id'] ] : array( 'returns' => array(), 'clicks' => array(), 'unsub' => array() );
              $pct  = function ( $n ) use ( $h ) {
                  return $h['sent'] ? $n . '人（' . round( 100 * $n / $h['sent'] ) . '%）' : $n . '人';
              };
              ?>
            <tr>
              <td><?php echo esc_html( $h['date'] ); ?></td>
              <td><?php echo esc_html( $h['subject'] ); ?><?php echo ! empty( $h['match'] ) ? '<br><span style="color:#646970">希望条件に合わせて送信</span>' : ''; ?></td>
              <td><?php echo esc_html( $h['jobs'] ); ?>件</td>
              <td><?php echo esc_html( $h['sent'] ); ?>人<?php echo $h['failed'] ? '<br><span style="color:#b32d2e">失敗' . esc_html( $h['failed'] ) . '人</span>' : ''; ?></td>
              <td><?php echo esc_html( isset( $h['skipped'] ) ? $h['skipped'] . '人' : '—' ); ?></td>
              <td><?php echo esc_html( $pct( count( $st['returns'] ) ) ); ?></td>
              <td><?php echo esc_html( $pct( count( $st['clicks'] ) ) ); ?></td>
              <td><?php echo esc_html( $pct( count( $st['unsub'] ) ) ); ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      <?php else : ?>
        <p>まだ配信していません。</p>
      <?php endif; ?>

      <h2>配信停止の手続き</h2>
      <p class="description">メールに「配信停止」と返信が来たら、そのメールアドレスをここに入れてください。</p>
      <form method="post">
        <?php wp_nonce_field( 'zaito_nl_unsub' ); ?>
        <input type="email" name="zaito_nl_unsub_email" required style="width:320px" placeholder="example@gmail.com">
        <button type="submit" class="button">配信停止にする</button>
      </form>
    </div>
    <script>
    (function () {
      var form = document.getElementById('znl-form');
      var status = document.getElementById('znl-status');
      var nonce = <?php echo wp_json_encode( wp_create_nonce( 'zaito_nl' ) ); ?>;
      var ajax = <?php echo wp_json_encode( admin_url( 'admin-ajax.php' ) ); ?>;
      var subject = document.getElementById('znl-subject');
      function picked() { return form.querySelectorAll('input[name="slugs[]"]:checked').length; }
      function autoSubject() { subject.value = '【zaito】新しい在宅求人が' + picked() + '件入りました'; }
      var touched = false;
      subject.addEventListener('input', function () { touched = true; });
      form.addEventListener('change', function (e) { if (e.target.name === 'slugs[]' && !touched) autoSubject(); });
      autoSubject();

      function post(action, extra) {
        var fd = new FormData(form);
        fd.append('action', action);
        fd.append('_ajax_nonce', nonce);
        Object.keys(extra || {}).forEach(function (k) { fd.append(k, extra[k]); });
        return fetch(ajax, { method: 'POST', body: fd, credentials: 'same-origin' }).then(function (r) { return r.json(); });
      }
      function fail(res) { status.style.color = '#b32d2e'; status.textContent = (res && res.data && res.data.message) || '失敗しました'; }

      document.getElementById('znl-preview').addEventListener('click', function () {
        post('zaito_nl_preview').then(function (res) {
          if (!res.success) return fail(res);
          var pre = document.getElementById('znl-body');
          pre.style.display = 'block';
          pre.textContent = '件名：' + res.data.subject + '\n\n' + res.data.body;
        });
      });
      document.getElementById('znl-test').addEventListener('click', function () {
        status.style.color = ''; status.textContent = 'テスト送信中…';
        post('zaito_nl_test').then(function (res) {
          if (!res.success) return fail(res);
          status.textContent = res.data.message;
        });
      });
      function loop() {
        post('zaito_nl_batch').then(function (res) {
          if (!res.success) return fail(res);
          status.style.color = '';
          status.textContent = '送信中… ' + res.data.next + ' / ' + res.data.total + '人（失敗 ' + res.data.failed + '人）。この画面を閉じないでください。';
          if (res.data.done) {
            status.style.color = '#1a7f55';
            status.textContent = '送信が完了しました：' + res.data.sent + '人に送信（希望に合う求人がなく送らなかった人 ' + res.data.skipped + '人、失敗 ' + res.data.failed + '人）';
            return;
          }
          setTimeout(loop, 1500);
        }, function () { status.textContent = '通信が切れました。画面を開き直すと、続きから送れます。'; });
      }
      document.getElementById('znl-send').addEventListener('click', function () {
        if (!picked()) { status.textContent = '求人を1件以上選んでください'; return; }
        if (!confirm('登録者全員に「' + subject.value + '」を送信します。よろしいですか？')) return;
        post('zaito_nl_start').then(function (res) {
          if (!res.success) return fail(res);
          loop();
        });
      });
      var resume = document.getElementById('znl-resume');
      if (resume) resume.addEventListener('click', loop);
    })();
    </script>
    <?php
}

/* ---------- 管理画面からの処理 ---------- */

function zaito_nl_check() {
    if ( ! current_user_can( 'manage_options' ) || ! check_ajax_referer( 'zaito_nl', false, false ) ) {
        wp_send_json_error( array( 'message' => '権限がありません。ページを開き直してください' ), 403 );
    }
}

function zaito_nl_read_form() {
    $slugs = isset( $_POST['slugs'] ) && is_array( $_POST['slugs'] ) ? array_map( 'sanitize_key', wp_unslash( $_POST['slugs'] ) ) : array();
    $slugs = array_values( array_filter( $slugs, 'zaito_remote_job' ) );
    $subject = isset( $_POST['subject'] ) ? sanitize_text_field( wp_unslash( $_POST['subject'] ) ) : '';
    $intro   = isset( $_POST['intro'] ) ? sanitize_textarea_field( wp_unslash( $_POST['intro'] ) ) : '';
    if ( ! $slugs ) {
        wp_send_json_error( array( 'message' => '求人を1件以上選んでください' ), 400 );
    }
    if ( '' === $subject ) {
        wp_send_json_error( array( 'message' => '件名を入力してください' ), 400 );
    }
    $campaign = current_time( 'YmdHi' );
    return array( $slugs, $subject, zaito_nl_build_body( $intro, $slugs, $campaign ), $intro, $campaign );
}

function zaito_nl_ajax_preview() {
    zaito_nl_check();
    list( , $subject, $body ) = zaito_nl_read_form();
    wp_send_json_success( array( 'subject' => $subject, 'body' => str_replace( '{UNSUB}', '（ここに一人ずつの配信停止リンクが入ります）', $body ) ) );
}
add_action( 'wp_ajax_zaito_nl_preview', 'zaito_nl_ajax_preview' );

function zaito_nl_ajax_test() {
    zaito_nl_check();
    list( , $subject, $body ) = zaito_nl_read_form();
    $to = isset( $_POST['test_to'] ) ? sanitize_email( wp_unslash( $_POST['test_to'] ) ) : '';
    if ( ! is_email( $to ) ) {
        wp_send_json_error( array( 'message' => 'テスト送信先のメールアドレスを確認してください' ), 400 );
    }
    $ok = zaito_nl_send_one( $to, '［テスト］' . $subject, $body, home_url( '/' ) . '（テストのため停止リンクはありません）' );
    wp_send_json_success( array( 'message' => $ok ? $to . ' にテスト送信しました。届いたメールを確認してください。' : '送信に失敗しました（サーバーのメール設定を確認してください）' ) );
}
add_action( 'wp_ajax_zaito_nl_test', 'zaito_nl_ajax_test' );

function zaito_nl_ajax_start() {
    zaito_nl_check();
    list( $slugs, $subject, , $intro, $campaign ) = zaito_nl_read_form();
    $to = zaito_nl_recipients();
    if ( ! $to ) {
        wp_send_json_error( array( 'message' => '送り先がいません' ), 400 );
    }
    update_option( 'zaito_nl_campaign', array(
        'id'      => $campaign,
        'subject' => $subject,
        'intro'   => $intro,
        'slugs'   => $slugs,
        'match'   => ! empty( $_POST['match'] ),
        'to'      => $to,
        'next'    => 0,
        'sent'    => 0,
        'failed'  => 0,
        'skipped' => 0,
        'started' => current_time( 'Y-m-d H:i' ),
    ), false );
    wp_send_json_success( array( 'total' => count( $to ) ) );
}
add_action( 'wp_ajax_zaito_nl_start', 'zaito_nl_ajax_start' );

function zaito_nl_ajax_batch() {
    zaito_nl_check();
    $c = get_option( 'zaito_nl_campaign' );
    if ( ! is_array( $c ) ) {
        wp_send_json_error( array( 'message' => '送信中の配信がありません' ), 400 );
    }
    $total = count( $c['to'] );
    $end   = min( $total, $c['next'] + ZAITO_NL_BATCH );
    for ( $i = $c['next']; $i < $end; $i++ ) {
        $id    = $c['to'][ $i ];
        $email = (string) get_post_meta( $id, 'email', true );
        // 送信を始めたあとに配信停止した人には送らない。
        if ( get_post_meta( $id, 'unsubscribed', true ) || ! is_email( $email ) ) {
            continue;
        }
        $slugs = $c['slugs'];
        if ( $c['match'] ) {
            $prefs = zaito_nl_member_prefs( $id );
            $slugs = array_values( array_filter( $slugs, function ( $slug ) use ( $prefs ) {
                $j = zaito_remote_job( $slug );
                return $j && zaito_nl_job_matches( $prefs, $j );
            } ) );
            if ( ! $slugs ) {
                $c['skipped']++; // 希望に合う求人がない人には送らない
                continue;
            }
        }
        $subject = $c['match'] ? '【zaito】あなたの希望に合う在宅求人が' . count( $slugs ) . '件入りました' : $c['subject'];
        $body    = zaito_nl_build_body( $c['intro'], $slugs, $c['id'], $id );
        if ( zaito_nl_send_one( $email, $subject, $body, zaito_nl_unsub_url( $id, $c['id'] ) ) ) {
            $c['sent']++;
        } else {
            $c['failed']++;
        }
    }
    $c['next'] = $end;
    $done      = $end >= $total;
    if ( $done ) {
        $history   = get_option( 'zaito_nl_history', array() );
        $history   = is_array( $history ) ? $history : array();
        $history[] = array(
            'id'      => $c['id'],
            'match'   => $c['match'],
            'skipped' => $c['skipped'],
            'date'    => $c['started'],
            'subject' => $c['subject'],
            'jobs'    => count( $c['slugs'] ),
            'sent'    => $c['sent'],
            'failed'  => $c['failed'],
        );
        update_option( 'zaito_nl_history', array_slice( $history, -50 ), false );
        $announced = get_option( 'zaito_nl_announced', array() );
        $announced = is_array( $announced ) ? $announced : array();
        foreach ( $c['slugs'] as $slug ) {
            $announced[ $slug ] = current_time( 'Y-m-d' );
        }
        update_option( 'zaito_nl_announced', $announced, false );
        delete_option( 'zaito_nl_campaign' );
    } else {
        update_option( 'zaito_nl_campaign', $c, false );
    }
    wp_send_json_success( array( 'next' => $end, 'total' => $total, 'sent' => $c['sent'], 'failed' => $c['failed'], 'skipped' => $c['skipped'], 'done' => $done ) );
}
add_action( 'wp_ajax_zaito_nl_batch', 'zaito_nl_ajax_batch' );
