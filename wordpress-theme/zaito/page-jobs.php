<?php get_header(); ?>

<main class="jobs-listing-main">
  <div class="wrap">
    <div class="jobs-header">
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
                value="<?php echo isset( $_GET['s'] ) ? esc_attr( $_GET['s'] ) : ''; ?>"
              />
            </div>

            <div class="filter-group">
              <label for="salary">時給の目安</label>
              <select id="salary" name="salary">
                <option value="">指定なし</option>
                <option value="1000" <?php selected( isset( $_GET['salary'] ) && $_GET['salary'] === '1000' ); ?>>1,000円以上</option>
                <option value="1500" <?php selected( isset( $_GET['salary'] ) && $_GET['salary'] === '1500' ); ?>>1,500円以上</option>
                <option value="2000" <?php selected( isset( $_GET['salary'] ) && $_GET['salary'] === '2000' ); ?>>2,000円以上</option>
              </select>
            </div>

            <div class="filter-group">
              <label for="type">仕事の種類</label>
              <select id="type" name="type">
                <option value="">全て</option>
                <option value="writing" <?php selected( isset( $_GET['type'] ) && $_GET['type'] === 'writing' ); ?>>ライティング</option>
                <option value="design" <?php selected( isset( $_GET['type'] ) && $_GET['type'] === 'design' ); ?>>デザイン</option>
                <option value="programming" <?php selected( isset( $_GET['type'] ) && $_GET['type'] === 'programming' ); ?>>プログラミング</option>
                <option value="support" <?php selected( isset( $_GET['type'] ) && $_GET['type'] === 'support' ); ?>>カスタマーサポート</option>
                <option value="data" <?php selected( isset( $_GET['type'] ) && $_GET['type'] === 'data' ); ?>>データ入力</option>
              </select>
            </div>

            <button type="submit" class="btn btn-accent btn-block">検索</button>
            <a href="<?php echo esc_url( home_url( '/jobs/' ) ); ?>" class="btn btn-outline btn-block">リセット</a>
          </form>
        </div>
      </aside>

      <div class="jobs-content">
        <?php
        $args = array(
            'post_type' => 'job_listing',
            'posts_per_page' => 12,
            'orderby' => 'date',
            'order' => 'DESC',
        );

        if ( ! post_type_exists( 'job_listing' ) ) {
            echo '<p class="empty-message">求人がまだありません。WP Job Manager プラグインをインストールしてください。</p>';
        } else {
            $jobs = get_posts( $args );

            if ( ! empty( $jobs ) ) :
        ?>
          <div class="jobs-list">
            <?php foreach ( $jobs as $job ) : ?>
              <a href="<?php echo esc_url( get_permalink( $job ) ); ?>" class="card">
                <div class="card-top">
                  <span class="badge badge-mint">人気</span>
                </div>
                <h3><?php echo esc_html( get_the_title( $job ) ); ?></h3>
                <p><?php echo esc_html( get_post_meta( $job->ID, '_company_name', true ) ); ?></p>
                <div class="price">
                  <span class="unit">時給 </span>
                  <?php echo esc_html( get_post_meta( $job->ID, '_job_salary', true ) ); ?>円
                </div>
                <div class="tag-list">
                  <span class="tag">#完全在宅</span>
                  <span class="tag">#未経験OK</span>
                </div>
                <div class="card-footer">
                  <span><?php echo esc_html( get_post_meta( $job->ID, '_job_days', true ) ); ?></span>
                  <span>大学生歓迎</span>
                </div>
              </a>
            <?php endforeach; ?>
          </div>
        <?php
            else :
                echo '<p class="empty-message">検索条件に合う求人がみつかりません</p>';
            endif;
        }
        ?>
      </div>
    </div>
  </div>
</main>

<?php get_footer(); ?>
