<?php get_header(); ?>

<main class="job-detail-main">
  <div class="wrap">
    <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
      <article class="job-detail">
        <div class="job-detail-header">
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
              <div class="job-info-grid">
                <div class="info-item">
                  <span class="label">時給</span>
                  <span class="value">
                    <?php
                    $salary = get_post_meta( get_the_ID(), '_job_salary', true );
                    echo $salary ? esc_html( $salary ) . '円' : '応相談';
                    ?>
                  </span>
                </div>
                <div class="info-item">
                  <span class="label">勤務形態</span>
                  <span class="value">
                    <?php echo esc_html( get_post_meta( get_the_ID(), '_job_type', true ) ); ?>
                  </span>
                </div>
                <div class="info-item">
                  <span class="label">最低勤務日数</span>
                  <span class="value">
                    <?php echo esc_html( get_post_meta( get_the_ID(), '_job_days', true ) ); ?>
                  </span>
                </div>
                <div class="info-item">
                  <span class="label">対象者</span>
                  <span class="value">
                    <?php echo esc_html( get_post_meta( get_the_ID(), '_job_target', true ) ); ?>
                  </span>
                </div>
              </div>
            </div>
          </div>

          <aside class="job-detail-sidebar">
            <div class="apply-card">
              <h3>この求人に応募する</h3>

              <?php if ( is_user_logged_in() ) : ?>
                <p class="user-email">
                  ログイン中：<strong><?php echo esc_html( wp_get_current_user()->user_email ); ?></strong>
                </p>
                <a href="<?php echo esc_url( home_url( '/apply/?job_id=' . get_the_ID() ) ); ?>" class="btn btn-accent btn-block">
                  応募する
                </a>
              <?php else : ?>
                <p class="login-prompt">応募にはログインが必要です</p>
                <a href="<?php echo esc_url( home_url( '/login/?redirect=' . urlencode( get_permalink() ) ) ); ?>" class="btn btn-accent btn-block">
                  ログイン
                </a>
                <p class="register-prompt">アカウントをお持ちでない方</p>
                <a href="<?php echo esc_url( home_url( '/register/' ) ); ?>" class="btn btn-outline btn-block">
                  無料で登録
                </a>
              <?php endif; ?>
            </div>

            <div class="share-card">
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
          );
          $related = get_posts( $related_args );
          foreach ( $related as $post ) :
              setup_postdata( $post );
          ?>
            <a href="<?php the_permalink(); ?>" class="card">
              <div class="card-top">
                <span class="badge badge-mint">人気</span>
              </div>
              <h3><?php the_title(); ?></h3>
              <p><?php echo esc_html( get_post_meta( get_the_ID(), '_company_name', true ) ); ?></p>
              <div class="price">
                <span class="unit">時給 </span>
                <?php echo esc_html( get_post_meta( get_the_ID(), '_job_salary', true ) ); ?>円
              </div>
              <div class="card-footer">
                <span>週2〜OK</span>
                <span>大学生歓迎</span>
              </div>
            </a>
          <?php endforeach; wp_reset_postdata(); ?>
        </div>
      </div>
    <?php endwhile; endif; ?>
  </div>
</main>

<?php get_footer(); ?>
