<?php
session_start();

if (!isset($_SESSION["kayitlar"])) {
    $_SESSION["kayitlar"] = [];
}

// Form gönderildiğinde yeni kayıt ekle
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $aciklama = trim($_POST["aciklama"] ?? "");
    $tur = $_POST["tur"] ?? "";
    $tutar = filter_var($_POST["tutar"] ?? "", FILTER_VALIDATE_FLOAT);

    if (
        $aciklama !== "" &&
        in_array($tur, ["gelir", "gider"], true) &&
        $tutar !== false &&
        $tutar > 0
    ) {
        $_SESSION["kayitlar"][] = [
            "tarih" => date("d.m.Y"),
            "aciklama" => $aciklama,
            "tur" => $tur,
            "tutar" => $tutar
        ];
    }

    header("Location: gelir-gider.php");
    exit;
}

$toplamGelir = 0;
$toplamGider = 0;

foreach ($_SESSION["kayitlar"] as $kayit) {
    if ($kayit["tur"] === "gelir") {
        $toplamGelir += $kayit["tutar"];
    } else {
        $toplamGider += $kayit["tutar"];
    }
}

$bakiye = $toplamGelir - $toplamGider;

function guvenliYazi($yazi) {
    return htmlspecialchars((string)$yazi, ENT_QUOTES, "UTF-8");
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gelir Gider Tablosu</title>
    <style>
        body { margin: 0; padding: 30px; font-family: Arial, sans-serif; background: #f2f4f7; color: #222; }
        .kutu { max-width: 850px; margin: auto; padding: 25px; background: white; border-radius: 10px; }
        h1 { margin-top: 0; }
        form { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 25px; }
        input, select, button { padding: 12px; font-size: 16px; }
        input[name="aciklama"] { flex: 1; min-width: 180px; }
        button { border: 0; border-radius: 5px; background: #287a4b; color: white; cursor: pointer; }
        .ozet { display: flex; flex-wrap: wrap; gap: 12px; margin-bottom: 25px; }
        .ozet-karti { flex: 1; min-width: 150px; padding: 15px; border-radius: 6px; background: #f2f4f7; }
        .ozet-karti p { margin: 5px 0; }
        .tablo-alani { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; border-bottom: 1px solid #ddd; text-align: left; }
        th { background: #f2f4f7; }
        .gelir { color: green; }
        .gider { color: #c0392b; }
        @media (max-width: 600px) { body { padding: 12px; } .kutu { padding: 18px; } }
    </style>
</head>
<body>
    <div class="kutu">
        <h1>Gelir Gider Tablosu</h1>
        <form method="post">
            <input type="text" name="aciklama" placeholder="Açıklama" required>
            <select name="tur">
                <option value="gelir">Gelir</option>
                <option value="gider">Gider</option>
            </select>
            <input type="number" name="tutar" placeholder="Tutar" min="0.01" step="0.01" required>
            <button type="submit">Ekle</button>
        </form>

        <div class="ozet">
            <div class="ozet-karti"><p>Toplam gelir</p><strong><?= number_format($toplamGelir, 2, ",", ".") ?> ₺</strong></div>
            <div class="ozet-karti"><p>Toplam gider</p><strong><?= number_format($toplamGider, 2, ",", ".") ?> ₺</strong></div>
            <div class="ozet-karti"><p>Kalan bakiye</p><strong><?= number_format($bakiye, 2, ",", ".") ?> ₺</strong></div>
        </div>

        <div class="tablo-alani">
            <table>
                <thead>
                    <tr><th>Tarih</th><th>Açıklama</th><th>Tür</th><th>Tutar</th></tr>
                </thead>
                <tbody>
                    <?php if (empty($_SESSION["kayitlar"])): ?>
                        <tr><td colspan="4">Henüz kayıt eklenmedi.</td></tr>
                    <?php else: ?>
                        <?php foreach ($_SESSION["kayitlar"] as $kayit): ?>
                            <tr>
                                <td><?= guvenliYazi($kayit["tarih"]) ?></td>
                                <td><?= guvenliYazi($kayit["aciklama"]) ?></td>
                                <td class="<?= guvenliYazi($kayit["tur"]) ?>"><?= $kayit["tur"] === "gelir" ? "Gelir" : "Gider" ?></td>
                                <td><?= number_format($kayit["tutar"], 2, ",", ".") ?> ₺</td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
