<?php
if ( ! is_user_logged_in() ) {
    wp_redirect( home_url( '/login/' ) );
    exit;
}
$current_user = wp_get_current_user();
if ( ! zaito_can_use_seeker_features( $current_user ) ) {
    wp_redirect( home_url( '/' ) );
    exit;
}
get_header();
?>

<main class="mypage-main">
  <div class="wrap">
    <?php zaito_render_verification_banner(); ?>
    <div class="mypage-header">
      <div class="header-avatar" aria-hidden="true"><?php echo esc_html( mb_substr( $current_user->first_name ?: $current_user->display_name, 0, 1 ) ); ?></div>
      <div>
        <div class="header-eyebrow">MY PAGE</div>
        <h1><?php echo esc_html( $current_user->first_name ); ?> さん</h1>
        <p>応募履歴とプロフィールの管理</p>
      </div>
    </div>

    <div class="mypage-grid">
      <aside class="mypage-sidebar">
        <div class="profile-card">
          <h3>プロフィール</h3>
          <div class="profile-item">
            <span class="label">氏名</span>
            <span class="value"><?php echo esc_html( $current_user->first_name ); ?></span>
          </div>
          <div class="profile-item">
            <span class="label">メールアドレス</span>
            <span class="value"><?php echo esc_html( $current_user->user_email ); ?></span>
          </div>
          <a href="<?php echo esc_url( home_url( '/worker-profile/' ) ); ?>" class="btn btn-outline">
            プロフィール編集
          </a>
        </div>

        <div class="menu-card">
          <h3>メニュー</h3>
          <a href="<?php echo esc_url( home_url( '/jobs/' ) ); ?>" class="menu-link">求人を探す</a>
          <a href="<?php echo esc_url( home_url( '/mypage/' ) ); ?>" class="menu-link active">応募履歴</a>
          <a href="<?php echo esc_url( home_url( '/worker-profile/' ) ); ?>" class="menu-link">プロフィール編集</a>
          <a href="<?php echo esc_url( home_url( '/chat/' ) ); ?>" class="menu-link">メッセージ</a>
          <a href="<?php echo esc_url( wp_logout_url( home_url( '/' ) ) ); ?>" class="menu-link logout">ログアウト</a>
        </div>
      </aside>

      <div class="mypage-content">
        <div class="content-section">
          <h2>応募履歴</h2>

          <?php
          $args = array(
              'post_type' => 'zaito_application',
              'posts_per_page' => 20,
              'orderby' => 'date',
              'order' => 'DESC',
              'meta_query' => array(
                  array(
                      'key' => 'applicant_id',
                      'value' => $current_user->ID,
                  ),
              ),
          );
          $applications = get_posts( $args );

          if ( ! empty( $applications ) ) :
          ?>
            <div class="applications-list">
              <?php foreach ( $applications as $app ) :
                  $job_id = get_post_meta( $app->ID, 'job_id', true );
                  $status = get_post_meta( $app->ID, 'status', true );
                  $job_title = get_the_title( $job_id );
                  $company_name = get_post_meta( $job_id, '_company_name', true );
              ?>
                <div class="application-item app-<?php echo esc_attr( $status ?: 'pending' ); ?>">
                  <div class="app-info">
                    <h3><?php echo esc_html( $job_title ); ?></h3>
                    <p class="company"><?php echo esc_html( $company_name ); ?></p>
                    <p class="date"><?php echo esc_html( get_the_date( 'Y/m/d', $app->ID ) ); ?> に応募</p>
                  </div>
                  <div class="app-status">
                    <span class="status-badge status-<?php echo esc_attr( $status ); ?>">
                      <?php
                      $status_label = array(
                          'pending' => '審査中',
                          'accepted' => '承認',
                          'rejected' => '非承認',
                      );
                      echo isset( $status_label[ $status ] ) ? $status_label[ $status ] : '不明';
                      ?>
                    </span>
                  </div>
                  <div class="app-actions">
                    <a href="<?php echo esc_url( get_permalink( $job_id ) ); ?>" class="btn btn-outline">
                      求人を見る
                    </a>
                    <?php if ( $status === 'accepted' ) : ?>
                      <a href="<?php echo esc_url( home_url( '/chat/?conversation_id=' . $app->ID ) ); ?>" class="btn btn-accent">
                        メッセージを送る
                      </a>
                    <?php endif; ?>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php else : ?>
            <p class="empty-message">応募履歴がありません</p>
            <a href="<?php echo esc_url( home_url( '/jobs/' ) ); ?>" class="btn btn-accent">
              求人を探す
            </a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</main>

<?php get_footer(); ?>
