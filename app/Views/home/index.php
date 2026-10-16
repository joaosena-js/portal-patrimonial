<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title>
        <?= htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8') ?>
    </title>

    <style>
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: Arial, sans-serif;
            background-color: #f4f6f9;
            color: #1f2937;
        }

        .container {
            width: 90%;
            max-width: 700px;
            padding: 40px;
            text-align: center;
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
        }

        h1 {
            margin-bottom: 12px;
            color: #123b66;
        }

        p {
            font-size: 18px;
            line-height: 1.6;
        }

        .status {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 18px;
            border-radius: 6px;
            color: #ffffff;
            background-color: #198754;
        }
    </style>
</head>

<body>
    <main class="container">
        <h1>
            <?= htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8') ?>
        </h1>

        <p>
            <?= htmlspecialchars($mensagem, ENT_QUOTES, 'UTF-8') ?>
        </p>

        <span class="status">
            Sistema funcionando
        </span>
    </main>
</body>
</html>