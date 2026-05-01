<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:sm="http://www.sitemaps.org/schemas/sitemap/0.9"
    xmlns:xhtml="http://www.w3.org/1999/xhtml">
<xsl:output method="html" encoding="UTF-8" indent="yes" doctype-system="about:legacy-compat"/>

<xsl:template match="/">
<html lang="tr">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <meta name="robots" content="noindex,follow"/>
    <title>XML Site Haritası — Tekcan Metal</title>
    <link rel="preconnect" href="https://fonts.googleapis.com"/>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="anonymous"/>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;600;700&amp;family=Inter:wght@400;500;600&amp;family=JetBrains+Mono:wght@400;500&amp;display=swap" rel="stylesheet"/>
    <style>
        :root {
            --navy-900: #050d24;
            --navy-800: #0c1e44;
            --navy-700: #143672;
            --gold-500: #c9a86b;
            --gold-400: #e0c48a;
            --gold-700: #a88a4a;
            --red: #c8102e;
            --paper: #fafaf7;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-500: #6b7280;
            --gray-700: #374151;
        }
        * { box-sizing: border-box; }
        body {
            font-family: 'Inter', -apple-system, system-ui, sans-serif;
            background: var(--paper);
            color: var(--navy-900);
            margin: 0;
            line-height: 1.6;
        }
        .header {
            background: linear-gradient(135deg, var(--navy-900) 0%, var(--navy-800) 50%, var(--navy-700) 100%);
            color: white;
            padding: 32px 24px;
            border-bottom: 4px solid var(--gold-500);
        }
        .header-content {
            max-width: 1280px;
            margin: 0 auto;
        }
        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 16px;
        }
        .crown {
            font-size: 28px;
            color: var(--gold-400);
        }
        .brand-name {
            font-family: 'Cormorant Garamond', Georgia, serif;
            font-size: 28px;
            font-weight: 700;
            letter-spacing: 0.5px;
        }
        h1 {
            font-family: 'Cormorant Garamond', Georgia, serif;
            font-size: 36px;
            font-weight: 600;
            margin: 0 0 8px 0;
            color: var(--gold-400);
        }
        .subtitle {
            color: rgba(255,255,255,0.7);
            font-size: 14px;
            font-family: 'JetBrains Mono', monospace;
        }
        .container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 32px 24px;
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 32px;
        }
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            border: 1px solid var(--gray-200);
            border-left: 4px solid var(--gold-500);
            box-shadow: 0 1px 2px rgba(0,0,0,0.04);
        }
        .stat-label {
            font-size: 12px;
            color: var(--gray-500);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
            font-weight: 600;
        }
        .stat-value {
            font-family: 'Cormorant Garamond', Georgia, serif;
            font-size: 32px;
            font-weight: 700;
            color: var(--navy-800);
            line-height: 1;
        }
        .stat-value.gold { color: var(--gold-700); }
        .stat-value.red { color: var(--red); }
        .filter-bar {
            display: flex;
            gap: 8px;
            margin-bottom: 16px;
            flex-wrap: wrap;
            align-items: center;
        }
        .search-input {
            flex: 1;
            min-width: 200px;
            padding: 10px 14px;
            border: 1px solid var(--gray-200);
            border-radius: 6px;
            font-size: 14px;
            font-family: inherit;
        }
        .search-input:focus {
            outline: none;
            border-color: var(--navy-700);
            box-shadow: 0 0 0 3px rgba(20,54,114,0.1);
        }
        .lang-filter {
            display: flex;
            gap: 4px;
        }
        .lang-btn {
            padding: 8px 14px;
            background: white;
            border: 1px solid var(--gray-200);
            border-radius: 6px;
            cursor: pointer;
            font-size: 13px;
            font-family: inherit;
            color: var(--gray-700);
            transition: all 0.15s;
            font-weight: 500;
        }
        .lang-btn:hover {
            background: var(--gray-100);
        }
        .lang-btn.active {
            background: var(--navy-800);
            color: white;
            border-color: var(--navy-800);
        }
        .table-wrapper {
            background: white;
            border-radius: 8px;
            border: 1px solid var(--gray-200);
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        thead {
            background: var(--navy-800);
            color: white;
        }
        thead th {
            padding: 14px 16px;
            text-align: left;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-family: 'Inter', sans-serif;
        }
        tbody tr {
            border-top: 1px solid var(--gray-200);
            transition: background 0.1s;
        }
        tbody tr:hover {
            background: #fafbfc;
        }
        tbody tr.hidden {
            display: none;
        }
        tbody td {
            padding: 12px 16px;
            font-size: 13px;
            vertical-align: middle;
        }
        td.url {
            font-family: 'JetBrains Mono', monospace;
            font-size: 12px;
        }
        td.url a {
            color: var(--navy-700);
            text-decoration: none;
            word-break: break-all;
        }
        td.url a:hover {
            color: var(--gold-700);
            text-decoration: underline;
        }
        .lang-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
            font-family: 'JetBrains Mono', monospace;
            text-transform: uppercase;
        }
        .lang-badge.tr { background: #fef3c7; color: #92400e; }
        .lang-badge.en { background: #dbeafe; color: #1e40af; }
        .lang-badge.ar { background: #d1fae5; color: #065f46; }
        .lang-badge.ru { background: #fee2e2; color: #991b1b; }
        td.lastmod {
            font-family: 'JetBrains Mono', monospace;
            font-size: 12px;
            color: var(--gray-500);
            white-space: nowrap;
        }
        td.priority {
            text-align: center;
        }
        .priority-bar {
            display: inline-block;
            width: 60px;
            height: 6px;
            background: var(--gray-200);
            border-radius: 3px;
            position: relative;
            overflow: hidden;
        }
        .priority-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--gold-500), var(--gold-700));
            border-radius: 3px;
        }
        .priority-text {
            font-family: 'JetBrains Mono', monospace;
            font-size: 11px;
            color: var(--gray-500);
            margin-top: 2px;
            display: block;
        }
        td.freq {
            font-size: 12px;
            color: var(--gray-500);
        }
        .footer {
            text-align: center;
            padding: 24px;
            color: var(--gray-500);
            font-size: 13px;
        }
        .footer a {
            color: var(--navy-700);
            text-decoration: none;
        }
        .row-num {
            font-family: 'JetBrains Mono', monospace;
            font-size: 11px;
            color: var(--gray-500);
            text-align: right;
            width: 50px;
        }
        @media (max-width: 768px) {
            .container { padding: 16px; }
            .stat-value { font-size: 24px; }
            h1 { font-size: 28px; }
            thead th, tbody td { padding: 10px 8px; font-size: 12px; }
            td.lastmod, td.freq { display: none; }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-content">
            <div class="brand">
                <span class="crown">👑</span>
                <span class="brand-name">Tekcan Metal</span>
            </div>
            <h1>XML Site Haritası</h1>
            <div class="subtitle">Bu sayfa arama motorları içindir. Toplam URL sayısı ve dağılım aşağıda gösterilmektedir.</div>
        </div>
    </header>

    <div class="container">
        <div class="stats">
            <div class="stat-card">
                <div class="stat-label">Toplam URL</div>
                <div class="stat-value" id="total"><xsl:value-of select="count(sm:urlset/sm:url)"/></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">🇹🇷 Türkçe</div>
                <div class="stat-value gold" id="count-tr">0</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">🇬🇧 İngilizce</div>
                <div class="stat-value" id="count-en">0</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">🇸🇦 Arapça</div>
                <div class="stat-value gold" id="count-ar">0</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">🇷🇺 Rusça</div>
                <div class="stat-value" id="count-ru">0</div>
            </div>
        </div>

        <div class="filter-bar">
            <input type="text" id="search" class="search-input" placeholder="🔍 URL'de ara... (örn: il, ihracat, sac, blog)"/>
            <div class="lang-filter">
                <button class="lang-btn active" data-lang="all" onclick="filterLang(this)">Tümü</button>
                <button class="lang-btn" data-lang="tr" onclick="filterLang(this)">🇹🇷 TR</button>
                <button class="lang-btn" data-lang="en" onclick="filterLang(this)">🇬🇧 EN</button>
                <button class="lang-btn" data-lang="ar" onclick="filterLang(this)">🇸🇦 AR</button>
                <button class="lang-btn" data-lang="ru" onclick="filterLang(this)">🇷🇺 RU</button>
            </div>
        </div>

        <div class="table-wrapper">
            <table id="sitemap-table">
                <thead>
                    <tr>
                        <th class="row-num">#</th>
                        <th>URL</th>
                        <th style="width:80px">Dil</th>
                        <th style="width:120px">Son Değişiklik</th>
                        <th style="width:120px">Sıklık</th>
                        <th style="width:100px">Öncelik</th>
                    </tr>
                </thead>
                <tbody>
                    <xsl:for-each select="sm:urlset/sm:url">
                        <xsl:variable name="url" select="sm:loc"/>
                        <xsl:variable name="lang">
                            <xsl:choose>
                                <xsl:when test="contains($url, '/en/')">en</xsl:when>
                                <xsl:when test="contains($url, '/ar/')">ar</xsl:when>
                                <xsl:when test="contains($url, '/ru/')">ru</xsl:when>
                                <xsl:otherwise>tr</xsl:otherwise>
                            </xsl:choose>
                        </xsl:variable>
                        <tr data-lang="{$lang}" data-url="{$url}">
                            <td class="row-num"><xsl:value-of select="position()"/></td>
                            <td class="url">
                                <a href="{sm:loc}" target="_blank" rel="noopener"><xsl:value-of select="sm:loc"/></a>
                            </td>
                            <td>
                                <span class="lang-badge {$lang}"><xsl:value-of select="$lang"/></span>
                            </td>
                            <td class="lastmod"><xsl:value-of select="sm:lastmod"/></td>
                            <td class="freq"><xsl:value-of select="sm:changefreq"/></td>
                            <td class="priority">
                                <div class="priority-bar">
                                    <div class="priority-fill">
                                        <xsl:attribute name="style">width: <xsl:value-of select="number(sm:priority) * 100"/>%;</xsl:attribute>
                                    </div>
                                </div>
                                <span class="priority-text"><xsl:value-of select="sm:priority"/></span>
                            </td>
                        </tr>
                    </xsl:for-each>
                </tbody>
            </table>
        </div>
    </div>

    <div class="footer">
        Tekcan Metal &#169; 2025 — <a href="/">Anasayfa</a> | <a href="/sitemap.xml">XML Görünümü</a>
    </div>

    <script>
        //<![CDATA[
        // Dil sayılarını hesapla
        var rows = document.querySelectorAll('#sitemap-table tbody tr');
        var counts = { tr: 0, en: 0, ar: 0, ru: 0 };
        rows.forEach(function(r) {
            var lang = r.getAttribute('data-lang');
            if (counts[lang] !== undefined) counts[lang]++;
        });
        document.getElementById('count-tr').textContent = counts.tr;
        document.getElementById('count-en').textContent = counts.en;
        document.getElementById('count-ar').textContent = counts.ar;
        document.getElementById('count-ru').textContent = counts.ru;

        // Filtre durumu
        var currentLang = 'all';
        var currentSearch = '';

        function applyFilters() {
            rows.forEach(function(r) {
                var lang = r.getAttribute('data-lang');
                var url = r.getAttribute('data-url').toLowerCase();
                var langOk = (currentLang === 'all' || lang === currentLang);
                var searchOk = (currentSearch === '' || url.indexOf(currentSearch) !== -1);
                r.classList.toggle('hidden', !(langOk && searchOk));
            });
        }

        function filterLang(btn) {
            document.querySelectorAll('.lang-btn').forEach(function(b) { b.classList.remove('active'); });
            btn.classList.add('active');
            currentLang = btn.getAttribute('data-lang');
            applyFilters();
        }

        document.getElementById('search').addEventListener('input', function(e) {
            currentSearch = e.target.value.toLowerCase().trim();
            applyFilters();
        });
        //]]>
    </script>
</body>
</html>
</xsl:template>
</xsl:stylesheet>
