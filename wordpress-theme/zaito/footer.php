  <footer class="site-footer">
    <div class="wrap site-footer-inner">
      <div class="site-footer-brand">
        <strong>zaito</strong>
        <p>完全在宅にこだわった在宅ワーク専門の求人サイトです。</p>
      </div>

      <nav class="site-footer-links">
        <a href="<?php echo esc_url( home_url( '/jobs/' ) ); ?>">求人を探す</a>
        <a href="<?php echo esc_url( home_url( '/for-companies/' ) ); ?>">企業の方へ</a>
        <a href="<?php echo esc_url( home_url( '/terms/' ) ); ?>">利用規約</a>
        <a href="<?php echo esc_url( home_url( '/privacy/' ) ); ?>">プライバシーポリシー</a>
        <a href="mailto:info@zaito-work.com">お問い合わせ</a>
      </nav>

      <div class="site-footer-meta">
        <p>運営: ZAITO運営事務局</p>
        <p>在宅ワーク求人サイト　© zaito <?php echo esc_html( date( 'Y' ) ); ?></p>
      </div>
    </div>
  </footer>

  <?php wp_footer(); ?>
</body>
</html>
