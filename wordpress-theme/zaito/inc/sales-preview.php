<?php
/**
 * 管理画面「求人 > 営業用仮ページ」: 営業で企業に送る仮ページ（_zaito_preview=1 の求人）の
 * URLを一覧で確認・コピーできるようにする。
 */

function zaito_sales_preview_menu() {
    add_submenu_page(
        'edit.php?post_type=job_listing',
        '営業用仮ページ',
        '営業用仮ページ',
        'edit_posts',
        'zaito-sales-previews',
        'zaito_render_sales_preview_page'
    );
}
add_action( 'admin_menu', 'zaito_sales_preview_menu' );

function zaito_render_sales_preview_page() {
    if ( ! current_user_can( 'edit_posts' ) ) {
        wp_die( 'この画面を表示する権限がありません。' );
    }

    $jobs = get_posts( array(
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
    ?>
    <div class="wrap">
      <h1>営業用仮ページ</h1>
      <p>企業に送る「掲載イメージ」ページの一覧です。一般には公開されておらず、URLを知っている人だけが見られます（検索エンジンにも載りません）。</p>
      <?php if ( ! $jobs ) : ?>
        <p>仮ページはまだありません。</p>
      <?php else : ?>
        <table class="widefat striped" style="max-width:1100px">
          <thead><tr><th style="width:24%">企業名</th><th style="width:28%">求人タイトル</th><th>URL（企業に送るもの）</th><th style="width:150px">操作</th></tr></thead>
          <tbody>
          <?php foreach ( $jobs as $job ) : $url = get_permalink( $job ); ?>
            <tr>
              <td><?php echo esc_html( get_post_meta( $job->ID, '_company_name', true ) ); ?></td>
              <td><?php echo esc_html( $job->post_title ); ?></td>
              <td><input type="text" readonly value="<?php echo esc_attr( $url ); ?>" onclick="this.select()" style="width:100%"></td>
              <td>
                <a class="button button-small" href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener">表示</a>
                <a class="button button-small" href="<?php echo esc_url( get_edit_post_link( $job->ID ) ); ?>">編集</a>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>
    <?php
}
