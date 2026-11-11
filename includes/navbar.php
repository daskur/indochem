<header class="site-header">
    <nav class="navbar container" aria-label="Main navigation">
        <a class="brand" href="<?= e(url('/index.php')) ?>" aria-label="<?= e(IPLA_APP_SHORT_NAME) ?> home">
            <span class="brand-mark" aria-hidden="true">IP</span>
            <span>
                <strong><?= e(IPLA_APP_SHORT_NAME) ?></strong>
                <small>Industrial Learning</small>
            </span>
        </a>
        <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="primary-navigation">
            <span class="sr-only">Toggle navigation</span>
            <span></span><span></span><span></span>
        </button>
        <div class="nav-menu" id="primary-navigation">
            <a href="<?= e(url('/index.php')) ?>">Home</a>
            <a href="<?= e(url('/news/index.php')) ?>">News</a>
            <a href="<?= e(url('/processes/index.php')) ?>">Processes</a>
            <a href="<?= e(url('/equipment/index.php')) ?>">Equipment</a>
            <a href="<?= e(url('/chemicals/index.php')) ?>">Chemicals</a>
            <a href="<?= e(url('/calculators/index.php')) ?>">Calculators</a>
            <a href="<?= e(url('/diagrams/index.php')) ?>">Diagrams</a>
            <a href="<?= e(url('/videos/index.php')) ?>">Videos</a>
            <a href="<?= e(url('/quizzes/index.php')) ?>">Quizzes</a>
            <a href="<?= e(url('/glossary/index.php')) ?>">Glossary</a>
            <a href="<?= e(url('/siwes/index.php')) ?>">SIWES</a>
            <?php if (is_logged_in()):
                $role = $_SESSION['user']['role'] ?? 'student';
            ?>
                <?php if ($role === 'admin'): ?>
                    <a href="<?= e(url('/admin/dashboard.php')) ?>">Admin</a>
                <?php endif; ?>
                <a href="<?= e(url('/profile/index.php')) ?>">Profile</a>
                <a href="<?= e(url('/bookmarks/index.php')) ?>">Bookmarks</a>
                <a href="<?= e(url('/certificates/index.php')) ?>">Certificates</a>
                <?php
                try {
                    $unreadStatement = $pdo->prepare('SELECT COUNT(*) FROM notifications WHERE user_id=? AND is_read=0');
                    $unreadStatement->execute([(int) current_user()['id']]);
                    $unreadNotifications = (int) $unreadStatement->fetchColumn();
                } catch (Throwable $exception) {
                    $unreadNotifications = 0;
                }
                ?>
                <a href="<?= e(url('/notifications/index.php')) ?>">Notifications<?= $unreadNotifications > 0 ? ' (' . $unreadNotifications . ')' : '' ?></a>
                    <form method="post" action="<?= e(url('/logout.php')) ?>" class="nav-logout">
                        <?= csrf_field() ?>
                        <button type="submit">Logout</button>
                    </form>
            <?php else: ?>
                <a href="<?= e(url('/login.php')) ?>" class="button button-primary button-small">Login</a>
            <?php endif; ?>
        </div>
    </nav>
</header>
