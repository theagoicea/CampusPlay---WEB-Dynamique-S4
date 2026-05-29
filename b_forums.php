<?php
session_start();
header('Content-Type: application/json');

// Vérification de connexion
$is_logged_in = isset($_SESSION['user_id']);

$host = 'localhost';
$dbname = 'campusmelody';
$username = 'root';
$password = 'root'; 

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'db_error', 'message' => "Erreur BDD"]);
    exit();
}

// Fonction pour le formatage du temps
function timeAgo($datetime) {
    $time = strtotime($datetime);
    $diff = time() - $time;
    if ($diff < 60) return "À l'instant";
    if ($diff < 3600) return floor($diff / 60) . " min";
    if ($diff < 86400) return "Il y a " . floor($diff / 3600) . "h";
    return "Il y a " . floor($diff / 86400) . "j";
}

// ==========================================
// CAS 1 : RECHERCHE EN DIRECT (Barre de recherche)
// ==========================================
if (isset($_GET['q'])) {
    $q = "%" . trim($_GET['q']) . "%";
    
    $stmt = $pdo->prepare("
        SELECT id_forum, titre 
        FROM forum 
        WHERE titre LIKE ? OR categorie LIKE ? OR id_forum IN (SELECT id_forum FROM message_forum WHERE contenu LIKE ?)
        ORDER BY date_creation DESC LIMIT 10
    ");
    $stmt->execute([$q, $q, $q]);
    
    // On renvoie un tableau simple pour le JS
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit();
}

// ==========================================
// CAS 2 : CHARGEMENT DE LA PAGE COMPLÈTE
// ==========================================

$forumCategories = [
    'general' => ['name' => 'Général', 'icon' => '💬'],
    'events'  => ['name' => 'Événements', 'icon' => '📅'],
    'jam'     => ['name' => 'Jam Sessions', 'icon' => '🎸'],
    'tech'    => ['name' => 'Technique & Matériel', 'icon' => '🎧'],
    'collab'  => ['name' => 'Collaborations', 'icon' => '🤝'],
];

// 1. Statistiques par catégorie
$catStats = [];
foreach ($forumCategories as $id => $data) {
    $catStats[$id] = ['topics' => 0, 'posts' => 0];
}

$stmtCats = $pdo->query("SELECT categorie, COUNT(*) as c FROM forum GROUP BY categorie");
while ($row = $stmtCats->fetch(PDO::FETCH_ASSOC)) {
    if (isset($catStats[$row['categorie']])) $catStats[$row['categorie']]['topics'] = $row['c'];
}

$stmtPosts = $pdo->query("SELECT f.categorie, COUNT(m.id_message) as c FROM forum f JOIN message_forum m ON f.id_forum = m.id_forum GROUP BY f.categorie");
while ($row = $stmtPosts->fetch(PDO::FETCH_ASSOC)) {
    if (isset($catStats[$row['categorie']])) $catStats[$row['categorie']]['posts'] = $row['c'];
}

// Préparation des catégories pour le JSON
$categoriesData = [];
foreach ($forumCategories as $id => $cat) {
    $categoriesData[] = [
        'id' => $id,
        'name' => $cat['name'],
        'icon' => $cat['icon'],
        'topics' => $catStats[$id]['topics'],
        'posts' => $catStats[$id]['posts']
    ];
}

// 2. Récupérer les discussions (Filtres et Recherche classique)
$cat_filter = $_GET['cat'] ?? null;
$search_query = $_GET['search'] ?? null;

$sqlTopics = "
    SELECT f.id_forum, f.titre, f.categorie, f.date_creation, f.est_ferme, u.prenom, u.nom,
           (SELECT COUNT(*) FROM message_forum m WHERE m.id_forum = f.id_forum) as replies
    FROM forum f
    JOIN utilisateur u ON f.id_createur = u.id_utilisateur
";
$paramsTopics = [];
$conditions = [];

if ($cat_filter && isset($forumCategories[$cat_filter])) {
    $conditions[] = "f.categorie = ?";
    $paramsTopics[] = $cat_filter;
}

if ($search_query) {
    $conditions[] = "(f.titre LIKE ? OR f.categorie LIKE ? OR f.id_forum IN (SELECT id_forum FROM message_forum WHERE contenu LIKE ?))";
    $like_search = "%" . $search_query . "%";
    $paramsTopics[] = $like_search;
    $paramsTopics[] = $like_search;
    $paramsTopics[] = $like_search;
}

if (!empty($conditions)) {
    $sqlTopics .= " WHERE " . implode(" AND ", $conditions);
}

$sqlTopics .= " ORDER BY f.date_creation DESC";
if (!$search_query && (!$cat_filter)) $sqlTopics .= " LIMIT 10";

$stmtTopics = $pdo->prepare($sqlTopics);
$stmtTopics->execute($paramsTopics);
$recentTopics = $stmtTopics->fetchAll(PDO::FETCH_ASSOC);

// Formatage des données pour simplifier l'affichage côté JS
foreach ($recentTopics as &$topic) {
    $topic['time_ago'] = timeAgo($topic['date_creation']);
    $topic['is_new'] = (time() - strtotime($topic['date_creation'])) < 86400;
    $topic['author'] = $topic['prenom'] . ' ' . mb_substr($topic['nom'], 0, 1) . '.';
    $topic['category_name'] = $forumCategories[$topic['categorie']]['name'] ?? 'Général';
}

// 3. Récupérer les stats globales
$totalTopics = $pdo->query("SELECT COUNT(*) FROM forum")->fetchColumn() ?: 0;
$totalPosts = $pdo->query("SELECT COUNT(*) FROM message_forum")->fetchColumn() ?: 0;
$activeMembers = $pdo->query("SELECT COUNT(DISTINCT id_auteur) FROM message_forum")->fetchColumn() ?: 0;

// Envoi de la réponse complète
echo json_encode([
    'success' => true,
    'is_logged_in' => $is_logged_in,
    'categories' => $categoriesData,
    'topics' => $recentTopics,
    'stats' => [
        'totalTopics' => $totalTopics,
        'totalPosts' => $totalPosts,
        'activeMembers' => $activeMembers
    ],
    'filters' => [
        'cat' => $cat_filter,
        'search' => $search_query,
        'cat_name' => $cat_filter ? $forumCategories[$cat_filter]['name'] : null
    ]
]);
exit();
?>
