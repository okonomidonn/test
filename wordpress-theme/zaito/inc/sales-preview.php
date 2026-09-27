<?php
/**
 * 管理画面「求人 > 営業用仮ページ」: 営業で企業に送る仮ページ（_zaito_preview=1 の求人）の
 * URLの確認・コピーと、営業の進み具合（未送信・返事待ち・掲載OK・見送り）の管理をする。
 *
 * 仮ページごとに次の情報を保存する（post meta）。
 * - _zaito_source_url   元の求人ページ（営業のきっかけにした募集）のURL
 * - _zaito_sales_status 営業状況（zaito_sales_statuses() のキー）
 * - _zaito_sales_date   送信日（Y-m-d）
 * - _zaito_sales_note   メモ（送り先・返事の内容など）
 */

function zaito_sales_statuses() {
    return array(
        'todo'    => array( '未送信', '#6B7590', '#F2F4F8' ),
        'waiting' => array( '返事待ち', '#9A5B00', '#FFF4E0' ),
        'won'     => array( '掲載OK', '#1A7F55', '#E6F6EE' ),
        'lost'    => array( '見送り', '#C8313B', '#FDF0F1' ),
    );
}

function zaito_sales_preview_menu() {
    add_submenu_page(
        'edit.php?post_type=job_listing',
        '営業管理',
        '営業管理',
        'edit_posts',
        'zaito-sales-previews',
        'zaito_render_sales_preview_page'
    );
}
add_action( 'admin_menu', 'zaito_sales_preview_menu' );

/**
 * 一覧の「保存」ボタンで送られた営業状況をまとめて保存する。
 */
function zaito_save_sales_rows() {
    $statuses = zaito_sales_statuses();
    $rows     = isset( $_POST['sales'] ) && is_array( $_POST['sales'] ) ? wp_unslash( $_POST['sales'] ) : array();
    foreach ( $rows as $post_id => $row ) {
        $post_id = (int) $post_id;
        if ( ! $post_id || 'job_listing' !== get_post_type( $post_id ) || '1' !== get_post_meta( $post_id, '_zaito_preview', true ) ) {
            continue;
        }
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            continue;
        }
        $status = isset( $row['status'] ) && isset( $statuses[ $row['status'] ] ) ? $row['status'] : 'todo';
        $date   = isset( $row['date'] ) && preg_match( '/^\d{4}-\d{2}-\d{2}$/', $row['date'] ) ? $row['date'] : '';
        // 「返事待ち」にしたのに送信日が空なら、今日の日付を入れておく。
        if ( 'waiting' === $status && '' === $date ) {
            $date = current_time( 'Y-m-d' );
        }
        update_post_meta( $post_id, '_zaito_sales_status', $status );
        update_post_meta( $post_id, '_zaito_sales_date', $date );
        update_post_meta( $post_id, '_zaito_sales_note', isset( $row['note'] ) ? sanitize_textarea_field( $row['note'] ) : '' );
        update_post_meta( $post_id, '_zaito_source_url', isset( $row['source'] ) ? esc_url_raw( trim( $row['source'] ) ) : '' );
    }
}

function zaito_render_sales_preview_page() {
    if ( ! current_user_can( 'edit_posts' ) ) {
        wp_die( 'この画面を表示する権限がありません。' );
    }

    $notice = '';
    // 「仮ページを作成する」ボタン: 登録されていない仮ページをその場で作成する。
    if ( isset( $_POST['zaito_create_previews'] ) ) {
        check_admin_referer( 'zaito_create_previews' );
        $created_links = zaito_upsert_preview_jobs();
        update_option( 'zaito_preview_jobs_version', ZAITO_PREVIEW_JOBS_VERSION );
        $notice = '仮ページを確認しました（' . count( $created_links ) . '件）。まだなかったものは新しく作成しました。';
    }
    if ( isset( $_POST['zaito_save_sales'] ) ) {
        check_admin_referer( 'zaito_save_sales' );
        zaito_save_sales_rows();
        $notice = '営業状況を保存しました。';
    }

    $statuses = zaito_sales_statuses();
    $jobs     = get_posts( array(
        'post_type'        => 'job_listing',
        'post_status'      => 'publish',
        'posts_per_page'   => -1,
        'orderby'          => 'date',
        'order'            => 'DESC',
        'suppress_filters' => true,
        'meta_query'       => array(
            array( 'key' => '_zaito_preview', 'value' => '1' ),
        ),
    ) );

    $counts = array_fill_keys( array_keys( $statuses ), 0 );
    foreach ( $jobs as $job ) {
        $st = get_post_meta( $job->ID, '_zaito_sales_status', true );
        $counts[ isset( $statuses[ $st ] ) ? $st : 'todo' ]++;
    }
    ?>
    <div class="wrap">
      <h1>営業管理</h1>
      <p>営業先ごとに、企業に送る「掲載イメージ」（仮ページ）と営業の進み具合を管理します。仮ページは一般には公開されておらず、URLを知っている人だけが見られます（検索エンジンにも載りません）。</p>
      <?php if ( $notice ) : ?>
        <div class="notice notice-success"><p><?php echo esc_html( $notice ); ?></p></div>
      <?php endif; ?>

      <div style="display:flex;flex-wrap:wrap;gap:8px;margin:16px 0">
        <?php foreach ( $statuses as $key => $st ) : ?>
          <span style="display:inline-flex;align-items:center;gap:8px;padding:6px 14px;border-radius:999px;background:<?php echo esc_attr( $st[2] ); ?>;color:<?php echo esc_attr( $st[1] ); ?>;font-weight:600">
            <?php echo esc_html( $st[0] ); ?> <span style="font-size:16px"><?php echo esc_html( $counts[ $key ] ); ?></span>
          </span>
        <?php endforeach; ?>
      </div>

      <form method="post" style="margin:0 0 16px">
        <?php wp_nonce_field( 'zaito_create_previews' ); ?>
        <button type="submit" name="zaito_create_previews" value="1" class="button">仮ページを作成する</button>
        <span class="description" style="margin-left:8px">一覧にない仮ページがあれば作成します。作成済みの仮ページの内容は変更しません。</span>
      </form>

      <?php if ( ! $jobs ) : ?>
        <p>仮ページはまだありません。</p>
      <?php else : ?>
        <form method="post">
          <?php wp_nonce_field( 'zaito_save_sales' ); ?>
          <table class="widefat striped">
            <thead><tr>
              <th style="width:17%">企業名・求人</th>
              <th style="width:12%">営業状況</th>
              <th style="width:11%">送信日</th>
              <th>仮ページのURL（企業に送るもの）</th>
              <th style="width:20%">元の求人ページ</th>
              <th style="width:18%">メモ</th>
            </tr></thead>
            <tbody>
            <?php foreach ( $jobs as $job ) :
                $id     = $job->ID;
                $url    = get_permalink( $job );
                $status = get_post_meta( $id, '_zaito_sales_status', true );
                $status = isset( $statuses[ $status ] ) ? $status : 'todo';
                $source = get_post_meta( $id, '_zaito_source_url', true );
            ?>
              <tr>
                <td>
                  <strong><?php echo esc_html( get_post_meta( $id, '_company_name', true ) ); ?></strong><br>
                  <span style="color:#646970"><?php echo esc_html( $job->post_title ); ?></span><br>
                  <a href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener">表示</a> ｜
                  <a href="<?php echo esc_url( get_edit_post_link( $id ) ); ?>">編集</a>
                </td>
                <td>
                  <select name="sales[<?php echo esc_attr( $id ); ?>][status]" style="width:100%;background:<?php echo esc_attr( $statuses[ $status ][2] ); ?>">
                    <?php foreach ( $statuses as $key => $st ) : ?>
                      <option value="<?php echo esc_attr( $key ); ?>" <?php selected( $status, $key ); ?>><?php echo esc_html( $st[0] ); ?></option>
                    <?php endforeach; ?>
                  </select>
                </td>
                <td><input type="date" name="sales[<?php echo esc_attr( $id ); ?>][date]" value="<?php echo esc_attr( get_post_meta( $id, '_zaito_sales_date', true ) ); ?>" style="width:100%"></td>
                <td><input type="text" readonly value="<?php echo esc_attr( $url ); ?>" onclick="this.select()" style="width:100%"></td>
                <td>
                  <input type="url" name="sales[<?php echo esc_attr( $id ); ?>][source]" value="<?php echo esc_attr( $source ); ?>" placeholder="https://" style="width:100%">
                  <?php if ( $source ) : ?>
                    <a href="<?php echo esc_url( $source ); ?>" target="_blank" rel="noopener noreferrer">開く</a>
                  <?php endif; ?>
                </td>
                <td><textarea name="sales[<?php echo esc_attr( $id ); ?>][note]" rows="2" style="width:100%" placeholder="送り先・返事の内容など"><?php echo esc_textarea( get_post_meta( $id, '_zaito_sales_note', true ) ); ?></textarea></td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
          <p><button type="submit" name="zaito_save_sales" value="1" class="button button-primary button-large">保存する</button></p>
        </form>
      <?php endif; ?>
    </div>
    <?php
}
