       <?php
        $loginResult = UserAccountService::authenticate(
            $pdo,
            $_POST['identifiant'] ?? null,
            $_POST['password'] ?? null
        );

        if (!empty($loginResult['success'])) {
            $redirectUrl = $postLoginRedirect ?? UserAccountService::dashboardUrl($loginResult['user'], $isAppConfig);
            header('Location: ' . $redirectUrl);
            exit();
        }

        echo "<span style='color:red;'>" . htmlspecialchars((string) ($loginResult['message'] ?? 'Identifiant ou mot de passe incorrect.'), ENT_QUOTES, 'UTF-8') . "</span>";
        ?>