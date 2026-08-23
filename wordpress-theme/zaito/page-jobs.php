<?php get_header();

$search_keyword = isset( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : '';
$search_salary  = isset( $_GET['salary'] ) ? sanitize_text_field( $_GET['salary'] ) : '';
$search_category = isset( $_GET['category'] ) ? sanitize_text_field( $_GET['category'] ) : '';
$paged = isset( $_GET['paged'] ) ? max( 1, intval( $_GET['paged'] ) ) : 1;
$per_page = 12;

$categories_list = array( 'ライティング', 'デザイン', 'プログラミング', '事務・データ入力', 'カスタマーサポート' );
?>

<main class="jobs-listing-main">
  <div class="wrap">
    <div class="jobs-header">
      <div class="section-eyebrow">JOB SEARCH</div>
      <h1>求人を探す</h1>
      <p>あなたに合った在宅ワークが見つかる</p>
    </div>

    <div class="jobs-grid">
      <aside class="jobs-sidebar">
        <div class="filter-card">
          <h3>検索条件</h3>
          <form method="get" class="filter-form">
            <div class="filter-group">
              <label for="search">キーワード</label>
              <input
                type="text"
                id="search"
                name="s"
                placeholder="仕事内容で検索"
                value="<?php echo esc_attr( $search_keyword ); ?>"
              />
            </div>

            <div class="filter-group">
              <label for="salary">時給の目安</label>
              <select id="salary" name="salary">
                <option value="">指定なし</option>
                <option value="1000" <?php selected( $search_salary, '1000' ); ?>>1,000円以上</option>
                <option value="1500" <?php selected( $search_salary, '1500' ); ?>>1,500円以上</option>
                <option value="2000" <?php selected( $search_salary, '2000' ); ?>>2,000円以上</option>
              </select>
            </div>

            <div class="filter-group">
              <label for="category">仕事の種類</label>
              <select id="category" name="category">
                <option value="">全て</option>
                <?php foreach ( $categories_list as $cat ) : ?>
                  <option value="<?php echo esc_attr( $cat ); ?>" <?php selected( $search_category, $cat ); ?>><?php echo esc_html( $cat ); ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <button type="submit" class="btn btn-accent btn-block">検索</button>
            <a href="<?php echo esc_url( home_url( '/jobs/' ) ); ?>" class="btn btn-outline btn-block">リセット</a>
          </form>
        </div>
      </aside>

      <div class="jobs-content">
        <?php
        if ( ! post_type_exists( 'job_listing' ) ) {
            echo '<p class="empty-message">求人がまだありません。</p>';
        } else {
            $args = array(
                'post_type'      => 'job_listing',
                'posts_per_page' => $per_page,
                'paged'          => $paged,
                'orderby'        => 'date',
                'order'          => 'DESC',
            );

            if ( $search_keyword ) {
                $args['s'] = $search_keyword;
            }

            $meta_query = array();
            if ( $search_salary ) {
                $meta_query[] = array(
                    'key'     => '_job_salary',
                    'value'   => intval( $search_salary ),
                    'compare' => '>=',
                    'type'    => 'NUMERIC',
                );
            }
            if ( $search_category ) {
                $meta_query[] = array(
                    'key'   => '_job_category',
                    'value' => $search_category,
                );
            }
            if ( ! empty( $meta_query ) ) {
                $args['meta_query'] = $meta_query;
            }

            $jobs_query = new WP_Query( $args );

            if ( $jobs_query->have_posts() ) :
        ?>
          <p class="jobs-result-count"><?php echo esc_html( $jobs_query->found_posts ); ?>件の求人が見つかりました</p>
          <div class="jobs-list">
            <?php while ( $jobs_query->have_posts() ) : $jobs_query->the_post();
                $job = get_post();
            ?>
              <a href="<?php echo esc_url( get_permalink( $job ) ); ?>" class="card">
                <div class="card-top">
                  <span class="badge <?php echo esc_attr( zaito_category_badge_class( get_post_meta( $job->ID, '_job_category', true ) ) ); ?>"><?php echo esc_html( get_post_meta( $job->ID, '_job_category', true ) ?: '人気' ); ?></span>
                </div>
                <h3><?php echo esc_html( get_the_title( $job ) ); ?></h3>
                <p><?php echo esc_html( get_post_meta( $job->ID, '_company_name', true ) ); ?></p>
                <div class="price">
                  <span class="unit">時給 </span>
                  <?php echo esc_html( get_post_meta( $job->ID, '_job_salary', true ) ); ?>円
                </div>
                <div class="tag-list">
                  <?php foreach ( zaito_job_tags( $job->ID ) as $tag ) : ?>
                    <span class="tag"><?php echo esc_html( $tag ); ?></span>
                  <?php endforeach; ?>
                </div>
                <div class="card-footer">
                  <span><?php echo esc_html( get_post_meta( $job->ID, '_job_days', true ) ); ?></span>
                  <span><?php echo esc_html( get_post_meta( $job->ID, '_job_target', true ) ); ?></span>
                </div>
              </a>
            <?php endwhile; wp_reset_postdata(); ?>
          </div>

          <?php if ( $jobs_query->max_num_pages > 1 ) : ?>
            <nav class="jobs-pagination">
              <?php if ( $paged > 1 ) : ?>
                <a href="<?php echo esc_url( add_query_arg( 'paged', $paged - 1 ) ); ?>" class="btn btn-outline btn-small">← 前へ</a>
              <?php endif; ?>
              <span class="jobs-pagination-status"><?php echo esc_html( $paged ); ?> / <?php echo esc_html( $jobs_query->max_num_pages ); ?> ページ</span>
              <?php if ( $paged < $jobs_query->max_num_pages ) : ?>
                <a href="<?php echo esc_url( add_query_arg( 'paged', $paged + 1 ) ); ?>" class="btn btn-outline btn-small">次へ →</a>
              <?php endif; ?>
            </nav>
          <?php endif; ?>
        <?php
            else :
                echo '<p class="empty-message">検索条件に合う求人がみつかりません</p>';
            endif;
            wp_reset_postdata();
        }
        ?>
      </div>
    </div>
  </div>
</main>

<?php get_footer(); ?>
