<?php

declare(strict_types=1);

/**
 * @param string $string
 * @return string
 */
function e(string $string): string
{
    return htmlspecialchars($string, ENT_QUOTES);
}

$domain = '';
if (!empty($_GET['domain'])) {
    $domain = $_GET['domain'];
}

if (!empty($domain)) {
    $stream_context = stream_context_create([
        'ssl' => [
            'capture_peer_cert_chain' => true,
            'verify_peer' => false,
            'verify_peer_name' => false,
        ],
    ]);

    $resource = stream_socket_client(
        "ssl://{$domain}:443",
        $error_code,
        $error_message,
        1,
        STREAM_CLIENT_CONNECT,
        $stream_context
    );

    $parsed = [];
    if (is_resource($resource)) {
        $context = stream_context_get_params($resource);
        $parsed = openssl_x509_parse($context['options']['ssl']['peer_certificate_chain'][0]);
    }
}
?>
<!doctype html>
<html lang="ja">

<head>
    <meta charset="utf-8">
    <title>SSL証明書設定確認ツール</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
</head>

<body>
    <div class="container">
        <div class="row">
            <div class="col"></div>
            <div class="col-lg-6">
                <h1 class="mt-3">SSL証明書設定確認ツール</h1>
                <div class="card">
                    <div class="card-body">
                        <form action="" method="get">
                            <label for="field_domain" class="col-form-label">ドメイン名</label>
                            <input type="text" name="domain" id="field_domain" class="form-control" value="<?= e($domain) ?>">
                            <button type="submit" class="btn btn-primary mt-3">チェック</button>
                            <a href="?domain=" class="btn btn-secondary mt-3">リセット</a>
                        </form>
                    </div>
                </div>
                <?php if (!empty($domain) && is_resource($resource)) : ?>
                    <div class="card mt-3">
                        <div class="card-body">
                            <h2><?= e($domain) ?></h2>
                            <table class="table table-striped m-0">
                                <tbody>
                                    <tr>
                                        <th>コモンネーム</th>
                                        <td>
                                            <?= !empty($parsed['subject']['CN']) ? e($parsed['subject']['CN']) : '---' ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>有効期限</th>
                                        <td>
                                            <?= !empty($parsed['validFrom_time_t']) ? e(date('Y-m-d H:i:s', $parsed['validFrom_time_t'])) : '---' ?>
                                            <span>〜</span>
                                            <?= !empty($parsed['validTo_time_t']) ? e(date('Y-m-d H:i:s', $parsed['validTo_time_t'])) : '---' ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>SAN</th>
                                        <td>
                                            <?= !empty($parsed['extensions']['subjectAltName']) ? $parsed['extensions']['subjectAltName'] : '---' ?>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php elseif (!empty($domain) && !is_resource($resource)) : ?>
                    <div class="card mt-3">
                        <div class="card-body">
                            <p class="m-0">接続に失敗しました</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <div class="col"></div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
</body>

</html>