<?php get_header(); ?>

<main class="job-detail-main">
  <div class="wrap">
    <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
      <article class="job-detail">
        <div class="job-detail-header">
          <?php $zaito_job_category = get_post_meta( get_the_ID(), '_job_category', true ); ?>
          <?php if ( $zaito_job_category ) : ?>
            <span class="badge <?php echo esc_attr( zaito_category_badge_class( $zaito_job_category ) ); ?>"><?php echo esc_html( $zaito_job_category ); ?></span>
          <?php endif; ?>
          <h1><?php the_title(); ?></h1>
          <p class="company"><?php echo esc_html( get_post_meta( get_the_ID(), '_company_name', true ) ); ?></p>
        </div>

        <div class="job-detail-grid">
          <div class="job-detail-content">
            <div class="detail-section">
              <h2>仕事内容</h2>
              <?php the_content(); ?>
            </div>

            <div class="detail-section">
              <h2>求人情報</h2>
              <?php
              $zaito_salary_type = get_post_meta( get_the_ID(), '_job_salary_type', true ) ?: '時給';
              $zaito_salary_min  = get_post_meta( get_the_ID(), '_job_salary', true );
              $zaito_salary_max  = get_post_meta( get_the_ID(), '_job_salary_max', true );
              $zaito_salary_text = '応相談';
              if ( $zaito_salary_min && $zaito_salary_max ) {
                  $zaito_salary_text = esc_html( $zaito_salary_min ) . '円〜' . esc_html( $zaito_salary_max ) . '円';
              } elseif ( $zaito_salary_min ) {
                  $zaito_salary_text = esc_html( $zaito_salary_min ) . '円〜';
              }
              ?>
              <div class="job-info-grid">
                <div class="job-info-item">
                  <span class="label">雇用形態</span>
                  <span class="value"><?php echo esc_html( get_post_meta( get_the_ID(), '_job_employment_type', true ) ?: '―' ); ?></span>
                </div>
                <div class="job-info-item">
                  <span class="label"><?php echo esc_html( $zaito_salary_type ); ?></span>
                  <span class="value"><?php echo $zaito_salary_text; ?></span>
                </div>
                <div class="job-info-item">
                  <span class="label">勤務体系</span>
                  <span class="value">
                    <?php echo esc_html( get_post_meta( get_the_ID(), '_job_type', true ) ?: '―' ); ?>
                  </span>
                </div>
                <div class="job-info-item">
                  <span class="label">最低勤務日数</span>
                  <span class="value">
                    <?php echo esc_html( get_post_meta( get_the_ID(), '_job_days', true ) ?: '応相談' ); ?>
                  </span>
                </div>
                <div class="job-info-item">
                  <span class="label">対象者</span>
                  <span class="value">
                    <?php echo esc_html( get_post_meta( get_the_ID(), '_job_target', true ) ?: '―' ); ?>
                  </span>
                </div>
              </div>
            </div>
          </div>

          <aside class="job-detail-sidebar">
            <div class="sidebar-card">
              <h3>この求人に応募する</h3>

              <?php if ( is_user_logged_in() && in_array( 'zaito_company', wp_get_current_user()->roles, true ) ) : ?>
                <p class="login-prompt">企業アカウントでログイン中のため応募できません。求職者としてご利用の場合は、一度ログアウトしてワーカーアカウントでログインしてください。</p>
                <a href="<?php echo esc_url( home_url( '/company/' ) ); ?>" class="btn btn-outline btn-block">
                  企業ダッシュボードへ
                </a>
              <?php elseif ( is_user_logged_in() && zaito_can_use_seeker_features( wp_get_current_user() ) ) :
                $zaito_already_applied = zaito_has_applied( wp_get_current_user()->ID, get_the_ID() );
              ?>
                <p class="user-email">
                  ログイン中：<strong><?php echo esc_html( wp_get_current_user()->user_email ); ?></strong>
                </p>
                <?php if ( $zaito_already_applied ) : ?>
                  <p class="login-prompt">この求人にはすでに応募済みです</p>
                  <a href="<?php echo esc_url( home_url( '/mypage/' ) ); ?>" class="btn btn-outline btn-block">
                    応募状況を見る
                  </a>
                <?php else : ?>
                  <a href="<?php echo esc_url( home_url( '/apply/?job_id=' . get_the_ID() ) ); ?>" class="btn btn-accent btn-block">
                    応募する
                  </a>
                <?php endif; ?>
              <?php else :
                $zaito_apply_redirect = home_url( '/apply/?job_id=' . get_the_ID() );
              ?>
                <p class="login-prompt">応募にはログインが必要です</p>
                <a href="<?php echo esc_url( add_query_arg( 'redirect_to', rawurlencode( $zaito_apply_redirect ), home_url( '/login/' ) ) ); ?>" class="btn btn-accent btn-block">
                  ログイン
                </a>
                <p class="register-prompt">アカウントをお持ちでない方</p>
                <a href="<?php echo esc_url( add_query_arg( 'redirect_to', rawurlencode( $zaito_apply_redirect ), home_url( '/register/' ) ) ); ?>" class="btn btn-outline btn-block">
                  無料で登録（1分で完了）
                </a>
              <?php endif; ?>
            </div>

            <div class="sidebar-card">
              <h3>この求人をシェア</h3>
              <div class="share-buttons">
                <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode( get_permalink() ); ?>" target="_blank" class="share-btn">
                  X
                </a>
                <a href="https://line.me/R/msg/text/?<?php echo urlencode( get_permalink() ); ?>" target="_blank" class="share-btn">
                  LINE
                </a>
              </div>
            </div>
          </aside>
        </div>
      </article>

      <div class="related-jobs">
        <h2>関連する求人</h2>
        <div class="card-grid">
          <?php
          $related_args = array(
              'post_type' => 'job_listing',
              'posts_per_page' => 3,
              'orderby' => 'rand',
              'post__not_in' => array( get_the_ID() ),
              // get_posts() はデフォルトでposts_whereフィルタが効かないため明示的に有効化
              'suppress_filters' => false,
          );
          $related = get_posts( $related_args );
          foreach ( $related as $post ) :
              setup_postdata( $post );
          ?>
            <a href="<?php the_permalink(); ?>" class="card">
              <div class="card-top">
                <?php $zaito_related_category = get_post_meta( get_the_ID(), '_job_category', true ); ?>
                <span class="badge <?php echo esc_attr( zaito_category_badge_class( $zaito_related_category ) ); ?>"><?php echo esc_html( $zaito_related_category ?: '人気' ); ?></span>
              </div>
              <h3><?php the_title(); ?></h3>
              <p><?php echo esc_html( get_post_meta( get_the_ID(), '_company_name', true ) ); ?></p>
              <div class="price">
                <span class="unit">時給 </span>
                <?php echo esc_html( get_post_meta( get_the_ID(), '_job_salary', true ) ); ?>円
              </div>
              <div class="card-footer">
                <span><?php echo esc_html( get_post_meta( get_the_ID(), '_job_days', true ) ?: '週2〜OK' ); ?></span>
                <span><?php echo esc_html( get_post_meta( get_the_ID(), '_job_target', true ) ?: '大学生歓迎' ); ?></span>
              </div>
            </a>
          <?php endforeach; wp_reset_postdata(); ?>
        </div>
      </div>
    <?php endwhile; endif; ?>
  </div>
</main>

<?php get_footer(); ?>
