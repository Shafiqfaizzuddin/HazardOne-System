<?php
require_once 'config.php';

$statement = $pdo->query(
    "SELECT * FROM hazards
     ORDER BY reported_at DESC"
);

$hazards = $statement->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HazardOne — Live Hazard Reports</title>
    <meta name="description" content="Real-time hazard reporting dashboard. Monitor road, environmental, and building hazards with GPS coordinates and user details.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@400;500;600;700&family=Inter:wght@300;400;500;600&family=Share+Tech+Mono&display=swap" rel="stylesheet">

    <style>
        /* ─── Reset & Base ─── */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg-base:      #080c14;
            --bg-card:      #0d1421;
            --bg-panel:     #111827;
            --border:       rgba(255,90,60,0.15);
            --border-glow:  rgba(255,90,60,0.4);
            --accent:       #ff5a3c;
            --accent2:      #ff8c00;
            --accent-green: #00e5a0;
            --accent-blue:  #3b82f6;
            --accent-purple:#a855f7;
            --text-primary: #f0f4ff;
            --text-muted:   #6b7a99;
            --text-dim:     #3d4d6b;

            --cat-road:    #ef4444;
            --cat-env:     #10b981;
            --cat-build:   #f97316;
            --cat-other:   #ec4899;
        }

        html { scroll-behavior: smooth; }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg-base);
            color: var(--text-primary);
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* ─── Animated Background Grid ─── */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background-image:
                linear-gradient(rgba(255,90,60,0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,90,60,0.03) 1px, transparent 1px);
            background-size: 48px 48px;
            pointer-events: none;
            z-index: 0;
        }

        body::after {
            content: '';
            position: fixed;
            top: -40%;
            left: -10%;
            width: 60%;
            height: 80%;
            background: radial-gradient(ellipse, rgba(255,90,60,0.07) 0%, transparent 70%);
            pointer-events: none;
            z-index: 0;
            animation: nebula 8s ease-in-out infinite alternate;
        }

        @keyframes nebula {
            from { transform: translate(0, 0) scale(1); }
            to   { transform: translate(5%, 3%) scale(1.05); }
        }

        /* ─── Header / Nav ─── */
        .navbar {
            position: sticky;
            top: 0;
            z-index: 100;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 2rem;
            height: 64px;
            background: rgba(8,12,20,0.85);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--border);
        }

        .nav-brand {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .nav-logo {
            width: 36px;
            height: 36px;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .nav-logo svg {
            width: 36px;
            height: 36px;
            filter: drop-shadow(0 0 8px var(--accent));
            animation: logoPulse 2.5s ease-in-out infinite;
        }

        @keyframes logoPulse {
            0%, 100% { filter: drop-shadow(0 0 6px var(--accent)); }
            50%       { filter: drop-shadow(0 0 16px var(--accent)) drop-shadow(0 0 30px rgba(255,90,60,0.4)); }
        }

        .nav-title {
            font-family: 'Rajdhani', sans-serif;
            font-size: 1.5rem;
            font-weight: 700;
            letter-spacing: 0.1em;
            color: var(--text-primary);
        }

        .nav-title span {
            color: var(--accent);
        }

        .nav-status {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.75rem;
            color: var(--accent-green);
            font-family: 'Share Tech Mono', monospace;
            letter-spacing: 0.05em;
        }

        .pulse-dot {
            width: 8px;
            height: 8px;
            background: var(--accent-green);
            border-radius: 50%;
            animation: pulseDot 1.5s ease-in-out infinite;
            box-shadow: 0 0 6px var(--accent-green);
        }

        @keyframes pulseDot {
            0%, 100% { opacity: 1; transform: scale(1); }
            50%       { opacity: 0.4; transform: scale(0.7); }
        }

        /* ─── Page Wrapper ─── */
        .page {
            position: relative;
            z-index: 1;
            padding: 2rem;
            max-width: 1600px;
            margin: 0 auto;
        }

        /* ─── Hero Section ─── */
        .hero {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 2rem;
            margin-bottom: 2.5rem;
            flex-wrap: wrap;
        }

        .hero-text h1 {
            font-family: 'Rajdhani', sans-serif;
            font-size: clamp(2rem, 4vw, 3rem);
            font-weight: 700;
            letter-spacing: 0.05em;
            line-height: 1.1;
            color: var(--text-primary);
        }

        .hero-text h1 .highlight {
            color: var(--accent);
            text-shadow: 0 0 20px rgba(255,90,60,0.5);
        }

        .hero-text p {
            margin-top: 0.6rem;
            color: var(--text-muted);
            font-size: 0.9rem;
            font-weight: 400;
            max-width: 480px;
            line-height: 1.6;
        }

        /* ─── Stat Cards ─── */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
            margin-bottom: 2.5rem;
        }

        .stat-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 1.2rem 1.4rem;
            position: relative;
            overflow: hidden;
            transition: border-color 0.3s, transform 0.2s;
            cursor: default;
        }

        .stat-card:hover {
            border-color: var(--border-glow);
            transform: translateY(-2px);
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 2px;
            background: var(--accent-color, var(--accent));
            box-shadow: 0 0 12px var(--accent-color, var(--accent));
        }

        .stat-card .stat-value {
            font-family: 'Rajdhani', sans-serif;
            font-size: 2.2rem;
            font-weight: 700;
            color: var(--accent-color, var(--accent));
            line-height: 1;
        }

        .stat-card .stat-label {
            font-size: 0.72rem;
            color: var(--text-muted);
            letter-spacing: 0.08em;
            text-transform: uppercase;
            margin-top: 4px;
        }

        .stat-card .stat-icon {
            position: absolute;
            top: 1rem; right: 1rem;
            font-size: 1.5rem;
            opacity: 0.15;
        }

        /* ─── Filter Bar ─── */
        .filter-bar {
            display: flex;
            gap: 0.6rem;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
            align-items: center;
        }

        .filter-label {
            font-size: 0.72rem;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-right: 0.4rem;
        }

        .filter-btn {
            padding: 6px 16px;
            border-radius: 99px;
            border: 1px solid var(--border);
            background: transparent;
            color: var(--text-muted);
            font-family: 'Inter', sans-serif;
            font-size: 0.78rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            letter-spacing: 0.03em;
        }

        .filter-btn:hover,
        .filter-btn.active {
            border-color: var(--accent);
            color: var(--text-primary);
            background: rgba(255,90,60,0.12);
            box-shadow: 0 0 10px rgba(255,90,60,0.15);
        }

        /* ─── Table Panel ─── */
        .table-panel {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 0 60px rgba(0,0,0,0.5), 0 0 30px rgba(255,90,60,0.05);
        }

        .table-panel-header {
            padding: 1.2rem 1.6rem;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: rgba(255,90,60,0.03);
        }

        .table-panel-header h2 {
            font-family: 'Rajdhani', sans-serif;
            font-size: 1rem;
            font-weight: 600;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: var(--text-muted);
        }

        .table-count {
            font-family: 'Share Tech Mono', monospace;
            font-size: 0.78rem;
            color: var(--accent);
            background: rgba(255,90,60,0.1);
            border: 1px solid rgba(255,90,60,0.25);
            padding: 3px 10px;
            border-radius: 99px;
        }

        /* ─── Table ─── */
        .table-wrapper {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead tr {
            background: rgba(255,90,60,0.05);
        }

        th {
            padding: 12px 16px;
            text-align: left;
            font-family: 'Rajdhani', sans-serif;
            font-size: 0.72rem;
            font-weight: 600;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: var(--text-muted);
            border-bottom: 1px solid var(--border);
            white-space: nowrap;
        }

        th .th-icon {
            margin-right: 5px;
            opacity: 0.7;
        }

        tbody tr {
            border-bottom: 1px solid rgba(255,90,60,0.06);
            transition: background 0.2s;
            animation: rowIn 0.4s ease both;
        }

        @keyframes rowIn {
            from { opacity: 0; transform: translateY(8px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        tbody tr:hover {
            background: rgba(255,90,60,0.05);
        }

        tbody tr:last-child {
            border-bottom: none;
        }

        td {
            padding: 14px 16px;
            font-size: 0.85rem;
            color: var(--text-primary);
            vertical-align: middle;
        }

        /* ─── User Cell ─── */
        .user-cell {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .user-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--accent), var(--accent2));
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Rajdhani', sans-serif;
            font-weight: 700;
            font-size: 0.85rem;
            color: #fff;
            flex-shrink: 0;
            box-shadow: 0 0 12px rgba(255,90,60,0.3);
        }

        .user-name {
            font-weight: 600;
            color: var(--text-primary);
        }

        /* ─── Time Cell ─── */
        .time-cell {
            font-family: 'Share Tech Mono', monospace;
            font-size: 0.78rem;
            color: var(--accent-green);
        }

        .time-date { color: var(--text-muted); font-size: 0.72rem; }

        /* ─── Device Cell ─── */
        .device-cell {
            font-size: 0.78rem;
            color: var(--text-muted);
            max-width: 220px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .device-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 0.65rem;
            font-weight: 600;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            margin-right: 6px;
            background: rgba(59,130,246,0.15);
            color: var(--accent-blue);
            border: 1px solid rgba(59,130,246,0.25);
        }

        /* ─── Location Cell ─── */
        .location-cell a {
            color: var(--accent-blue);
            text-decoration: none;
            font-size: 0.85rem;
            transition: color 0.2s;
        }

        .location-cell a:hover {
            color: #93c5fd;
            text-decoration: underline;
        }

        /* ─── Coords Cell ─── */
        .coords-cell {
            font-family: 'Share Tech Mono', monospace;
            font-size: 0.75rem;
            color: var(--text-muted);
        }

        .coords-cell a {
            color: inherit;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 4px;
            transition: color 0.2s;
        }

        .coords-cell a:hover { color: var(--accent-green); }

        /* ─── Category Badge ─── */
        .cat-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 12px;
            border-radius: 99px;
            font-size: 0.72rem;
            font-weight: 600;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .cat-road        { background: rgba(239,68,68,0.12);  color: var(--cat-road);  border: 1px solid rgba(239,68,68,0.3); }
        .cat-environmental { background: rgba(16,185,129,0.12); color: var(--cat-env);  border: 1px solid rgba(16,185,129,0.3); }
        .cat-building    { background: rgba(249,115,22,0.12); color: var(--cat-build);  border: 1px solid rgba(249,115,22,0.3); }
        .cat-other       { background: rgba(236,72,153,0.12); color: var(--cat-other);  border: 1px solid rgba(236,72,153,0.3); }

        .cat-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: currentColor;
            animation: catDotPulse 2s ease-in-out infinite;
        }

        @keyframes catDotPulse {
            0%, 100% { opacity: 1; }
            50%       { opacity: 0.35; }
        }

        /* ─── Description Cell ─── */
        .desc-cell {
            max-width: 200px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            font-size: 0.83rem;
            color: var(--text-muted);
            cursor: help;
        }

        /* ─── Empty State ─── */
        .empty-state {
            padding: 5rem 2rem;
            text-align: center;
        }

        .empty-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.3;
            animation: floatIcon 3s ease-in-out infinite;
        }

        @keyframes floatIcon {
            0%, 100% { transform: translateY(0); }
            50%       { transform: translateY(-10px); }
        }

        .empty-state h3 {
            font-family: 'Rajdhani', sans-serif;
            font-size: 1.5rem;
            color: var(--text-dim);
            letter-spacing: 0.08em;
        }

        .empty-state p {
            font-size: 0.85rem;
            color: var(--text-dim);
            margin-top: 0.4rem;
        }

        /* ─── Footer ─── */
        .footer {
            margin-top: 2.5rem;
            padding: 1.2rem 0;
            border-top: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 0.72rem;
            color: var(--text-dim);
            font-family: 'Share Tech Mono', monospace;
            letter-spacing: 0.04em;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        /* ─── Scrollbar ─── */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: var(--bg-base); }
        ::-webkit-scrollbar-thumb { background: rgba(255,90,60,0.2); border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: rgba(255,90,60,0.4); }

        /* ─── Responsive ─── */
        @media (max-width: 768px) {
            .page { padding: 1rem; }
            .hero { margin-bottom: 1.5rem; }
            .stats-row { grid-template-columns: repeat(2, 1fr); }
            .navbar { padding: 0 1rem; }
        }
    </style>
</head>
<body>

<?php
/* ─── Pre-compute stats ─── */
$total   = count($hazards);
$roads   = 0;
$envs    = 0;
$builds  = 0;
$others  = 0;
foreach ($hazards as $h) {
    switch ($h['category']) {
        case 'Road':          $roads++;  break;
        case 'Environmental': $envs++;   break;
        case 'Building':      $builds++; break;
        default:              $others++; break;
    }
}

function catClass(string $cat): string {
    return match($cat) {
        'Road'          => 'cat-road',
        'Environmental' => 'cat-environmental',
        'Building'      => 'cat-building',
        default         => 'cat-other',
    };
}

function catIcon(string $cat): string {
    return match($cat) {
        'Road'          => '🛣️',
        'Environmental' => '🌿',
        'Building'      => '🏗️',
        default         => '⚠️',
    };
}

function deviceBadge(string $ua): string {
    if (stripos($ua, 'Android') !== false) return 'Android';
    if (stripos($ua, 'iOS') !== false || stripos($ua, 'iPhone') !== false) return 'iOS';
    if (stripos($ua, 'Windows') !== false) return 'Windows';
    if (stripos($ua, 'Linux') !== false) return 'Linux';
    return 'Device';
}
?>

<!-- ══ NAVBAR ══ -->
<nav class="navbar">
    <div class="nav-brand">
        <div class="nav-logo">
            <svg viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg">
                <polygon points="18,2 34,32 2,32" fill="none" stroke="#ff5a3c" stroke-width="2.5" stroke-linejoin="round"/>
                <line x1="18" y1="13" x2="18" y2="23" stroke="#ff5a3c" stroke-width="3" stroke-linecap="round"/>
                <circle cx="18" cy="28" r="1.8" fill="#ff5a3c"/>
            </svg>
        </div>
        <span class="nav-title">Hazard<span>One</span></span>
    </div>
    <div class="nav-status">
        <span class="pulse-dot"></span>
        LIVE MONITORING
    </div>
</nav>

<!-- ══ PAGE ══ -->
<div class="page">

    <!-- HERO -->
    <div class="hero">
        <div class="hero-text">
            <h1>Hazard <span class="highlight">Command</span> Center</h1>
            <p>Real-time field hazard reports streamed directly from the ground. Monitor, analyze, and act.</p>
        </div>
    </div>

    <!-- STAT CARDS -->
    <div class="stats-row">
        <div class="stat-card" style="--accent-color: var(--accent)">
            <div class="stat-icon">🚨</div>
            <div class="stat-value"><?= $total ?></div>
            <div class="stat-label">Total Reports</div>
        </div>
        <div class="stat-card" style="--accent-color: var(--cat-road)">
            <div class="stat-icon">🛣️</div>
            <div class="stat-value"><?= $roads ?></div>
            <div class="stat-label">Road Hazards</div>
        </div>
        <div class="stat-card" style="--accent-color: var(--cat-env)">
            <div class="stat-icon">🌿</div>
            <div class="stat-value"><?= $envs ?></div>
            <div class="stat-label">Environmental</div>
        </div>
        <div class="stat-card" style="--accent-color: var(--cat-build)">
            <div class="stat-icon">🏗️</div>
            <div class="stat-value"><?= $builds ?></div>
            <div class="stat-label">Building</div>
        </div>
        <?php if ($others > 0): ?>
        <div class="stat-card" style="--accent-color: var(--cat-other)">
            <div class="stat-icon">⚠️</div>
            <div class="stat-value"><?= $others ?></div>
            <div class="stat-label">Other</div>
        </div>
        <?php endif; ?>
    </div>

    <!-- FILTER BAR -->
    <div class="filter-bar">
        <span class="filter-label">Filter:</span>
        <button class="filter-btn active" id="btn-all" onclick="filterRows('all')">All</button>
        <button class="filter-btn" id="btn-road"          onclick="filterRows('Road')">🛣️ Road</button>
        <button class="filter-btn" id="btn-environmental" onclick="filterRows('Environmental')">🌿 Environmental</button>
        <button class="filter-btn" id="btn-building"      onclick="filterRows('Building')">🏗️ Building</button>
    </div>

    <!-- TABLE PANEL -->
    <div class="table-panel">
        <div class="table-panel-header">
            <h2>📡 Incident Feed</h2>
            <span class="table-count"><?= $total ?> report<?= $total !== 1 ? 's' : '' ?></span>
        </div>

        <div class="table-wrapper">
            <?php if ($total === 0): ?>
            <div class="empty-state">
                <div class="empty-icon">🛡️</div>
                <h3>ALL CLEAR — NO HAZARDS REPORTED</h3>
                <p>The incident feed is empty. Stand by for incoming reports.</p>
            </div>
            <?php else: ?>
            <table id="hazard-table">
                <thead>
                    <tr>
                        <th><span class="th-icon">👤</span>Reporter</th>
                        <th><span class="th-icon">🕐</span>Date & Time</th>
                        <th><span class="th-icon">📱</span>Device</th>
                        <th><span class="th-icon">📍</span>Location</th>
                        <th><span class="th-icon">🌐</span>GPS</th>
                        <th><span class="th-icon">🏷️</span>Category</th>
                        <th><span class="th-icon">📝</span>Description</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($hazards as $i => $hazard):
                    $cat   = htmlspecialchars($hazard['category']);
                    $lat   = htmlspecialchars($hazard['latitude']);
                    $lng   = htmlspecialchars($hazard['longitude']);
                    $name  = htmlspecialchars($hazard['user_name']);
                    $initials = mb_strtoupper(mb_substr($name, 0, 1)) . (mb_strlen($name) > 1 ? mb_strtoupper(mb_substr($name, 1, 1)) : '');
                    $reported = htmlspecialchars($hazard['reported_at']);
                    [$datepart, $timepart] = array_pad(explode(' ', $reported, 2), 2, '');
                    $ua    = htmlspecialchars($hazard['user_agent']);
                    $loc   = htmlspecialchars($hazard['location_name']);
                    $desc  = htmlspecialchars($hazard['description']);
                    $mapsUrl = "https://www.google.com/maps?q={$lat},{$lng}";
                    $delay = ($i * 60) . 'ms';
                ?>
                <tr data-category="<?= $cat ?>" style="animation-delay:<?= $delay ?>">
                    <!-- Reporter -->
                    <td>
                        <div class="user-cell">
                            <div class="user-avatar"><?= $initials ?></div>
                            <span class="user-name"><?= $name ?></span>
                        </div>
                    </td>

                    <!-- Date & Time -->
                    <td>
                        <div class="time-cell"><?= $timepart ?></div>
                        <div class="time-date"><?= $datepart ?></div>
                    </td>

                    <!-- Device -->
                    <td>
                        <span class="device-badge"><?= deviceBadge($hazard['user_agent']) ?></span>
                        <span class="device-cell" title="<?= $ua ?>"><?= $ua ?></span>
                    </td>

                    <!-- Location -->
                    <td class="location-cell">
                        <a href="<?= $mapsUrl ?>" target="_blank" rel="noopener" title="Open in Google Maps">
                            📍 <?= $loc ?>
                        </a>
                    </td>

                    <!-- GPS -->
                    <td class="coords-cell">
                        <a href="<?= $mapsUrl ?>" target="_blank" rel="noopener" title="View on map">
                            <span>🌐</span>
                            <?= $lat ?>, <?= $lng ?>
                        </a>
                    </td>

                    <!-- Category -->
                    <td>
                        <span class="cat-badge <?= catClass($hazard['category']) ?>">
                            <span class="cat-dot"></span>
                            <?= catIcon($cat) ?> <?= $cat ?>
                        </span>
                    </td>

                    <!-- Description -->
                    <td>
                        <span class="desc-cell" title="<?= $desc ?>"><?= $desc ?></span>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>

    <!-- FOOTER -->
    <footer class="footer">
        <span>⚡ HAZARDONE v1.0 — FIELD OPERATIONS DASHBOARD</span>
        <span id="live-clock"></span>
    </footer>
</div>

<script>
/* ── Live Clock ── */
function updateClock() {
    const now = new Date();
    const pad = n => String(n).padStart(2, '0');
    document.getElementById('live-clock').textContent =
        `SYS TIME: ${pad(now.getHours())}:${pad(now.getMinutes())}:${pad(now.getSeconds())}`;
}
updateClock();
setInterval(updateClock, 1000);

/* ── Filter ── */
function filterRows(category) {
    document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));

    const btnId = category === 'all' ? 'btn-all'
        : 'btn-' + category.toLowerCase();
    const btn = document.getElementById(btnId);
    if (btn) btn.classList.add('active');

    document.querySelectorAll('#hazard-table tbody tr').forEach(row => {
        if (category === 'all' || row.dataset.category === category) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}
</script>

</body>
</html>