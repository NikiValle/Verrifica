<?php
session_start();
if(!isset($_SESSION['logged'])){header("Location: login.php");exit();}
require_once __DIR__ . '/db.php';
$flash = null;
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $mId = filter_input(INPUT_POST, 'm', FILTER_VALIDATE_INT);
    $cId = filter_input(INPUT_POST, 'c', FILTER_VALIDATE_INT);
    if (!$mId || !$cId) {
        $flash = ['type' => 'error', 'msg' => 'Selezione non valida.'];
    } else {
        try {
            $exists = $conn->prepare("SELECT 1 FROM Iscrizioni_Corsi WHERE id_membro = ? AND id_corso = ? LIMIT 1");
            $exists->execute([$mId, $cId]);
            if ($exists->fetchColumn()) {
                $flash = ['type' => 'info', 'msg' => 'Questo membro è già iscritto a questo corso.'];
            } else {
                $ins = $conn->prepare(
                    "INSERT INTO Iscrizioni_Corsi (id_corso, id_membro, data_iscrizione, orario_preferito)
                     VALUES (?, ?, CURDATE(), NULL)"
                );
                $ins->execute([$cId, $mId]);
                $info = $conn->prepare(
                    "SELECT Membri.nome AS m, Corsi.nome_corso AS c, Istruttori.nome AS i
                     FROM Membri
                     JOIN Corsi ON Corsi.id_corso = ?
                     JOIN Istruttori ON Corsi.id_istruttore = Istruttori.id_istruttore
                     WHERE Membri.id_membro = ?"
                );
                $info->execute([$cId, $mId]);
                $r = $info->fetch();
                if ($r) {
                    $m = htmlspecialchars($r['m'] ?? '', ENT_QUOTES, 'UTF-8');
                    $cname = htmlspecialchars($r['c'] ?? '', ENT_QUOTES, 'UTF-8');
                    $i = htmlspecialchars($r['i'] ?? '', ENT_QUOTES, 'UTF-8');
                    $flash = ['type' => 'success', 'msg' => "Iscrizione aggiunta: {$m} → {$cname} ({$i})."];
                } else {
                    $flash = ['type' => 'success', 'msg' => 'Iscrizione aggiunta.'];
                }
            }
        } catch (PDOException $e) {
            $flash = ['type' => 'error', 'msg' => 'Errore durante l’iscrizione: ' . $e->getMessage()];
        }
    }
}
$m=$conn->query("SELECT id_membro,nome FROM Membri");
$c=$conn->query("SELECT Corsi.id_corso,nome_corso,Istruttori.nome i FROM Corsi JOIN Istruttori ON Corsi.id_istruttore=Istruttori.id_istruttore");
$topCoursesStmt = $conn->query(
    "SELECT t.id_istruttore,
            t.nome_istruttore,
            t.cognome_istruttore,
            t.id_corso,
            t.nome_corso,
            t.iscritti
     FROM (
        SELECT c.id_istruttore,
               i.nome AS nome_istruttore,
               i.cognome AS cognome_istruttore,
               c.id_corso,
               c.nome_corso,
               COUNT(ic.id_iscrizione) AS iscritti
        FROM Corsi c
        JOIN Istruttori i ON i.id_istruttore = c.id_istruttore
        JOIN Iscrizioni_Corsi ic ON ic.id_corso = c.id_corso
        GROUP BY c.id_istruttore, i.nome, i.cognome, c.id_corso, c.nome_corso
     ) t
     JOIN (
        SELECT id_istruttore, MAX(iscritti) AS max_iscritti
        FROM (
            SELECT c.id_istruttore,
                   c.id_corso,
                   COUNT(ic.id_iscrizione) AS iscritti
            FROM Corsi c
            JOIN Iscrizioni_Corsi ic ON ic.id_corso = c.id_corso
            GROUP BY c.id_istruttore, c.id_corso
        ) x
        GROUP BY id_istruttore
     ) mx ON mx.id_istruttore = t.id_istruttore AND mx.max_iscritti = t.iscritti
     WHERE t.iscritti >= 5
     ORDER BY t.cognome_istruttore, t.nome_istruttore, t.iscritti DESC, t.nome_corso"
);
$topCourses = $topCoursesStmt->fetchAll();
?>
<?php if ($flash): ?>
    <p>
        <?php
            $msg = htmlspecialchars($flash['msg'], ENT_QUOTES, 'UTF-8');
            echo $msg;
        ?>
    </p>
<?php endif; ?>
<form method=post>
<select name=m><?php foreach($m as $x) echo "<option value=\"" . htmlspecialchars($x['id_membro'], ENT_QUOTES, 'UTF-8') . "\">" . htmlspecialchars($x['nome'], ENT_QUOTES, 'UTF-8') . "</option>";?></select>
<select name=c><?php foreach($c as $x) echo "<option value=\"" . htmlspecialchars($x['id_corso'], ENT_QUOTES, 'UTF-8') . "\">" . htmlspecialchars($x['nome_corso'], ENT_QUOTES, 'UTF-8') . " - " . htmlspecialchars($x['i'], ENT_QUOTES, 'UTF-8') . "</option>";?></select>
<button>OK</button>
</form>
<?php if ($topCourses): ?>
    <h3>Corso con più iscritti per istruttore (min 5)</h3>
    <ul>
        <?php foreach ($topCourses as $row): ?>
            <li>
                <?php
                    $istr = htmlspecialchars(($row['nome_istruttore'] ?? '') . ' ' . ($row['cognome_istruttore'] ?? ''), ENT_QUOTES, 'UTF-8');
                    $corso = htmlspecialchars($row['nome_corso'] ?? '', ENT_QUOTES, 'UTF-8');
                    $n = htmlspecialchars((string)($row['iscritti'] ?? ''), ENT_QUOTES, 'UTF-8');
                    echo "{$istr}: {$corso} ({$n} iscritti)";
                ?>
            </li>
        <?php endforeach; ?>
    </ul>
<?php else: ?>
    <h3>Corso con più iscritti per istruttore (min 5)</h3>
    <p>Nessun istruttore ha un corso con almeno 5 iscritti.</p>
<?php endif; ?>