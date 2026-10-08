<?php
/**
 * 新着求人メールの一斉配信（管理画面「求人 > 新着メール配信」）。
 *
 * - 送り先: 「興味あり登録」(zaito_interest) のメールアドレス全員。配信停止した人は除く。
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
        if ( get_post_meta( $id, 'unsubscribed', true ) ) {
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

function zaito_nl_unsub_url( $id ) {
    return add_query_arg( array( 'zaito_unsub' => (int) $id, 'sig' => zaito_nl_unsub_sig( $id ) ), home_url( '/' ) );
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

/* ---------- メールの中身 ---------- */

function zaito_nl_job_url( $slug, $campaign ) {
    return add_query_arg( array(
        'utm_source'   => 'newsletter',
        'utm_medium'   => 'email',
        'utm_campaign' => $campaign,
    ), zaito_remote_url( $slug ) );
}

/**
 * 本文。{UNSUB} は送るときに一人ずつの停止リンクに置き換える。
 */
function zaito_nl_build_body( $intro, $slugs, $campaign ) {
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
            . '　' . zaito_nl_job_url( $slug, $campaign ) . "\n\n";
    }
    $body .= "すべての在宅求人を見る\n"
        . add_query_arg( array( 'utm_source' => 'newsletter', 'utm_medium' => 'email', 'utm_campaign' => $campaign ), home_url( '/' ) ) . "\n\n"
        . "──────────\n"
        . "このメールは、zaitoに登録したメールアドレスにお送りしています。\n"
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
        <p><button type="button" class="button button-primary button-large" id="znl-send">登録者 <?php echo esc_html( $recipients ); ?>人に送信する</button></p>
        <p id="znl-status" style="font-size:14px;font-weight:600"></p>
      </form>

      <h2>配信の記録</h2>
      <?php if ( $history ) : ?>
        <table class="widefat striped" style="max-width:960px">
          <thead><tr><th style="width:160px">日時</th><th>件名</th><th style="width:90px">求人</th><th style="width:90px">送信</th><th style="width:90px">失敗</th></tr></thead>
          <tbody>
          <?php foreach ( array_reverse( $history ) as $h ) : ?>
            <tr><td><?php echo esc_html( $h['date'] ); ?></td><td><?php echo esc_html( $h['subject'] ); ?></td><td><?php echo esc_html( $h['jobs'] ); ?>件</td><td><?php echo esc_html( $h['sent'] ); ?>人</td><td><?php echo esc_html( $h['failed'] ); ?>人</td></tr>
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
            status.textContent = '送信が完了しました：' + res.data.sent + '人に送信（失敗 ' + res.data.failed + '人）';
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
    $campaign = current_time( 'Ymd' );
    return array( $slugs, $subject, zaito_nl_build_body( $intro, $slugs, $campaign ) );
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
    list( $slugs, $subject, $body ) = zaito_nl_read_form();
    $to = zaito_nl_recipients();
    if ( ! $to ) {
        wp_send_json_error( array( 'message' => '送り先がいません' ), 400 );
    }
    update_option( 'zaito_nl_campaign', array(
        'subject' => $subject,
        'body'    => $body,
        'slugs'   => $slugs,
        'to'      => $to,
        'next'    => 0,
        'sent'    => 0,
        'failed'  => 0,
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
        if ( zaito_nl_send_one( $email, $c['subject'], $c['body'], zaito_nl_unsub_url( $id ) ) ) {
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
    wp_send_json_success( array( 'next' => $end, 'total' => $total, 'sent' => $c['sent'], 'failed' => $c['failed'], 'done' => $done ) );
}
add_action( 'wp_ajax_zaito_nl_batch', 'zaito_nl_ajax_batch' );
