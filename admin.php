<?php
session_start();
include "connection.php";



// معالجة الموافقة أو الرفض
if(isset($_GET['action'], $_GET['id'], $_GET['type'])){
    $id = intval($_GET['id']);
    $type = $_GET['type']; // company / instructor
    $action = $_GET['action']; // approve / reject

    if($type === 'company'){
        $table = 'companies';
        $id_field = 'company_id';
    } elseif($type === 'instructor'){
        $table = 'instructors';
        $id_field = 'instructor_id';
    }

    if(isset($table)){
        $status = $action === 'approve' ? 'approved' : 'rejected';
        $stmt = $conn->prepare("UPDATE $table SET status=? WHERE $id_field=?");
        $stmt->bind_param("si", $status, $id);
        $stmt->execute();
    }

   
    
}

// جلب بيانات الشركات والمدرسين
$companies = $conn->query("SELECT company_id, company_name, email, phone, industry, status FROM companies ORDER BY created_at DESC");
$instructors = $conn->query("SELECT instructor_id, CONCAT(first_name, ' ', last_name) as full_name, email, phone, department, university_name, status FROM instructors ORDER BY created_at DESC");

// Calculate statistics
$stats_sql = "SELECT 
    (SELECT COUNT(*) FROM companies WHERE status = 'pending') as pending_companies,
    (SELECT COUNT(*) FROM companies WHERE status = 'approved') as approved_companies,
    (SELECT COUNT(*) FROM instructors WHERE status = 'pending') as pending_instructors,
    (SELECT COUNT(*) FROM instructors WHERE status = 'approved') as approved_instructors,
    (SELECT COUNT(*) FROM companies) as total_companies,
    (SELECT COUNT(*) FROM instructors) as total_instructors";
$stats_result = $conn->query($stats_sql);
$stats = $stats_result->fetch_assoc();

// Reset pointers for tables
$companies->data_seek(0);
$instructors->data_seek(0);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin Dashboard – Approval Management</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&display=swap" rel="stylesheet">
  <style>
  /* ===== Global Reset ===== */
  * { margin: 0; padding: 0; box-sizing: border-box; }

  body {
    font-family: "Inter", sans-serif;
    background:
      radial-gradient(60rem 60rem at -10% -20%, rgba(14,165,168,0.06), transparent 60%),
      radial-gradient(50rem 50rem at 120% -10%, rgba(11,31,58,0.06), transparent 60%),
      #f6f8fb;
    color: #0f172a;
    overflow-x: hidden;
    line-height: 1.7;
    scroll-behavior: smooth;
  }

  :root { --brand:#0ea5a8; --brand-2:#22d3ee; --ink:#0b1f3a; --muted:#475569; --panel:#ffffff; --line:#e5e7eb; }
  ::selection { background: rgba(14,165,168,0.3); }

  /* Topbar */
  header {
    background: #ffffff;
    color: var(--ink);
    padding: 14px 5vw;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid var(--line);
    position: sticky;
    top: 0;
    z-index: 10;
    transition: box-shadow 0.2s ease;
  }
  header.scrolled { box-shadow: 0 8px 24px rgba(2,6,23,0.06); }

  header h1 {
    font-size: 1.4rem;
    font-weight: 800;
    letter-spacing: 0.2px;
  }

  .logout-btn {
    background: #ff4757;
    color: white;
    padding: 8px 16px;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.2s ease;
    text-decoration: none;
  }

  .logout-btn:hover {
    background: #ff3742;
  }

  /* Hero */
  .hero {
    text-align: left;
    padding: 90px 5vw;
    position: relative;
    color: #ffffff;
    background:var(--ink);
    overflow: hidden;
  }
  .hero::before { content:""; position:absolute; inset:0; background:url('https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?q=80&w=1600&auto=format&fit=crop') center/cover no-repeat; filter:brightness(0.55); }
  .hero::after { content:""; position:absolute; inset:0; background:linear-gradient(90deg, rgba(11,31,58,0.85) 0%, rgba(14,165,168,0.35) 100%); background-size:200% 100%; animation:gradientShift 12s ease-in-out infinite alternate; }
  .hero-inner { position:relative; z-index:1; max-width: 1100px; }
  .hero h2 { font-size: clamp(2rem, 4vw, 3rem); color:#fff; margin-bottom: 12px; }
  .hero p { font-size: 1.05rem; color: #e2e8f0; max-width: 760px; }

  /* Statistics Section */
  .stats-section {
    padding: 40px 5vw;
    max-width: 1200px;
    margin: 0 auto;
  }

  .stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 40px;
  }

  .stat-card {
    background: linear-gradient(135deg, rgba(14,165,168,0.05), rgba(34,211,238,0.05));
    border: 1px solid rgba(14,165,168,0.1);
    border-radius: 16px;
    padding: 24px 20px;
    text-align: center;
    transition: transform 0.25s ease, box-shadow 0.25s ease;
    position: relative;
    overflow: hidden;
  }

  .stat-card::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, var(--brand), var(--brand-2));
  }

  .stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 28px rgba(14,165,168,0.15);
  }

  .stat-number {
    font-size: 2.5rem;
    font-weight: 900;
    color: var(--brand);
    margin-bottom: 8px;
    text-shadow: 0 2px 4px rgba(14,165,168,0.1);
  }

  .stat-label {
    font-size: 0.9rem;
    color: var(--muted);
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }

  /* Section Header */
  .section-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 2rem;
    padding: 1.5rem;
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
    border-radius: 16px;
    border: 1px solid #e2e8f0;
  }

  .section-icon {
    font-size: 2.5rem;
    background: linear-gradient(135deg, var(--brand) 0%, var(--brand-2) 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
  }

  .section-content {
    flex: 1;
  }

  .section-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--ink);
    margin-bottom: 0.5rem;
  }

  .section-subtitle {
    color: var(--muted);
    font-size: 1rem;
    margin: 0;
  }

  /* Modern Card */
  .card {
    background: var(--panel);
    border: 1px solid var(--line);
    border-radius: 16px;
    padding: 32px;
    margin-bottom: 40px;
    box-shadow: 0 10px 24px rgba(2,6,23,0.05);
    position: relative;
    overflow: hidden;
    transition: transform 0.25s ease, box-shadow 0.25s ease;
  }

  .card::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    height: 4px;
    width: 100%;
    background: linear-gradient(90deg, var(--brand), var(--brand-2));
  }

  .card:hover {
    transform: translateY(-4px);
    box-shadow: 0 16px 32px rgba(2,6,23,0.08);
  }

  .card h2 {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--ink);
    margin-bottom: 24px;
    display: flex;
    align-items: center;
    gap: 12px;
  }

  /* Table Styles */
  .table-container {
    overflow-x: auto;
    border-radius: 12px;
    border: 1px solid var(--line);
  }

  table {
    width: 100%;
    border-collapse: collapse;
    background: white;
  }

  th {
    background: linear-gradient(135deg, var(--brand) 0%, var(--brand-2) 100%);
    color: white;
    padding: 16px 20px;
    text-align: left;
    font-weight: 600;
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }

  td {
    padding: 16px 20px;
    border-bottom: 1px solid var(--line);
    color: var(--ink);
  }

  tr:last-child td {
    border-bottom: none;
  }

  tr:hover {
    background: rgba(14,165,168,0.05);
  }

  /* Status Badge */
  .status {
    display: inline-block;
    padding: 6px 14px;
    border-radius: 20px;
    font-weight: 600;
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }

  .status.pending {
    background: linear-gradient(135deg, #ffa500 0%, #ff8c00 100%);
    color: white;
  }

  .status.approved {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
  }

  .status.rejected {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    color: white;
  }

  /* Action Buttons */
  .action-buttons {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
  }

  .btn {
    padding: 10px 20px;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    font-weight: 600;
    font-size: 0.9rem;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-block;
  }

  .btn-approve {
    background: linear-gradient(135deg, var(--brand) 0%, var(--brand-2) 100%);
    color: white;
  }

  .btn-approve:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 16px rgba(14,165,168,0.3);
  }

  .btn-reject {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    color: white;
  }

  .btn-reject:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 16px rgba(239,68,68,0.3);
  }

  /* Empty State */
  .empty-state {
    text-align: center;
    padding: 60px 20px;
    color: var(--muted);
  }

  .empty-state-icon {
    font-size: 4rem;
    margin-bottom: 1rem;
    opacity: 0.5;
  }

  .empty-state h3 {
    font-size: 1.2rem;
    margin-bottom: 0.5rem;
    color: var(--ink);
  }

  /* Enhanced Instructor Section */
  .instructor-card {
    background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
    border: 2px solid #e2e8f0;
    border-left: 5px solid var(--brand);
    border-radius: 16px;
    padding: 32px;
    margin-bottom: 40px;
    box-shadow: 0 10px 24px rgba(2,6,23,0.05);
    position: relative;
    overflow: hidden;
    transition: all 0.3s ease;
    background-image: 
      radial-gradient(circle at 20% 50%, rgba(14,165,168,0.03) 0%, transparent 50%),
      radial-gradient(circle at 80% 80%, rgba(34,211,238,0.03) 0%, transparent 50%);
  }

  .instructor-card::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    height: 5px;
    width: 100%;
    background: linear-gradient(90deg, var(--brand) 0%, var(--brand-2) 50%, #8b5cf6 100%);
    animation: gradientShift 3s ease-in-out infinite;
  }

  .instructor-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 20px 40px rgba(14,165,168,0.15);
    border-color: var(--brand);
  }

  .instructor-section-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 2rem;
    padding: 1.5rem;
    background: linear-gradient(135deg, rgba(14,165,168,0.08) 0%, rgba(34,211,238,0.08) 100%);
    border-radius: 16px;
    border: 1px solid rgba(14,165,168,0.2);
    position: relative;
    overflow: hidden;
  }

  .instructor-section-header::before {
    content: "";
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
    animation: shimmer 3s infinite;
  }

  .instructor-section-icon {
    font-size: 3rem;
    background: linear-gradient(135deg, var(--brand) 0%, var(--brand-2) 50%, #8b5cf6 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    filter: drop-shadow(0 2px 4px rgba(14,165,168,0.2));
    animation: pulse 2s ease-in-out infinite;
  }

  @keyframes shimmer {
    0% { left: -100%; }
    100% { left: 100%; }
  }

  @keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.05); }
  }

  /* Enhanced Instructor Table */
  .instructor-table-container {
    overflow-x: auto;
    border-radius: 16px;
    border: 2px solid rgba(14,165,168,0.1);
    background: white;
    box-shadow: 0 4px 12px rgba(14,165,168,0.08);
  }

  .instructor-table-container table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
  }

  .instructor-table-container th {
    background: linear-gradient(135deg, var(--brand) 0%, var(--brand-2) 50%, #8b5cf6 100%);
    color: white;
    padding: 18px 24px;
    text-align: left;
    font-weight: 700;
    font-size: 0.95rem;
    text-transform: uppercase;
    letter-spacing: 1px;
    position: relative;
    text-shadow: 0 2px 4px rgba(0,0,0,0.1);
  }

  .instructor-table-container th:first-child {
    border-top-left-radius: 14px;
  }

  .instructor-table-container th:last-child {
    border-top-right-radius: 14px;
  }

  .instructor-table-container td {
    padding: 20px 24px;
    border-bottom: 1px solid rgba(14,165,168,0.08);
    color: var(--ink);
    transition: all 0.2s ease;
    position: relative;
  }

  .instructor-table-container tr {
    transition: all 0.3s ease;
  }

  .instructor-table-container tr:hover {
    background: linear-gradient(90deg, rgba(14,165,168,0.05) 0%, rgba(34,211,238,0.05) 100%);
    transform: scale(1.01);
    box-shadow: 0 4px 12px rgba(14,165,168,0.1);
  }

  .instructor-table-container tr:hover td {
    padding-left: 28px;
    color: var(--ink);
    font-weight: 500;
  }

  .instructor-table-container tr:last-child td {
    border-bottom: none;
  }

  .instructor-table-container tr:last-child:hover td:first-child {
    border-bottom-left-radius: 14px;
  }

  .instructor-table-container tr:last-child:hover td:last-child {
    border-bottom-right-radius: 14px;
  }

  /* Enhanced Instructor Info Cells */
  .instructor-name {
    font-weight: 700;
    font-size: 1.05rem;
    color: var(--ink);
    display: flex;
    align-items: center;
    gap: 10px;
  }

  .instructor-name::before {
    content: "👤";
    font-size: 1.2rem;
    opacity: 0.7;
  }

  .instructor-email {
    color: var(--brand);
    font-weight: 500;
    text-decoration: none;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 6px;
  }

  .instructor-email::before {
    content: "✉";
    font-size: 0.9rem;
  }

  .instructor-email:hover {
    color: var(--brand-2);
    text-decoration: underline;
  }

  .instructor-phone {
    color: var(--muted);
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-weight: 500;
  }

  .instructor-phone::before {
    content: "📞";
    font-size: 0.9rem;
  }

  .instructor-department {
    background: linear-gradient(135deg, rgba(14,165,168,0.1) 0%, rgba(34,211,238,0.1) 100%);
    padding: 6px 14px;
    border-radius: 20px;
    font-weight: 600;
    color: var(--brand);
    display: inline-block;
    border: 1px solid rgba(14,165,168,0.2);
  }

  .instructor-university {
    font-weight: 600;
    color: var(--ink);
    display: inline-flex;
    align-items: center;
    gap: 6px;
  }

  .instructor-university::before {
    content: "🏛";
    font-size: 1rem;
  }

  /* Enhanced Status Badge for Instructors */
  .instructor-status {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    border-radius: 25px;
    font-weight: 700;
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    position: relative;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
  }

  .instructor-status::before {
    content: "";
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
    transition: left 0.5s ease;
  }

  .instructor-status:hover::before {
    left: 100%;
  }

  .instructor-status.pending {
    background: linear-gradient(135deg, #ffa500 0%, #ff8c00 100%);
    color: white;
  }

  .instructor-status.pending::after {
    content: "⏳";
    margin-left: 4px;
  }

  .instructor-status.approved {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
  }

  .instructor-status.approved::after {
    content: "✅";
    margin-left: 4px;
  }

  .instructor-status.rejected {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    color: white;
  }

  .instructor-status.rejected::after {
    content: "❌";
    margin-left: 4px;
  }

  /* Enhanced Action Buttons for Instructors */
  .instructor-action-buttons {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
  }

  .instructor-btn {
    padding: 12px 24px;
    border: none;
    border-radius: 12px;
    cursor: pointer;
    font-weight: 700;
    font-size: 0.9rem;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    position: relative;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
  }

  .instructor-btn::before {
    content: "";
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    border-radius: 50%;
    background: rgba(255,255,255,0.3);
    transform: translate(-50%, -50%);
    transition: width 0.6s, height 0.6s;
  }

  .instructor-btn:hover::before {
    width: 300px;
    height: 300px;
  }

  .instructor-btn-approve {
    background: linear-gradient(135deg, var(--brand) 0%, var(--brand-2) 100%);
    color: white;
  }

  .instructor-btn-approve:hover {
    transform: translateY(-3px) scale(1.05);
    box-shadow: 0 8px 20px rgba(14,165,168,0.4);
  }

  .instructor-btn-approve::after {
    content: "✓";
    font-size: 1.1rem;
  }

  .instructor-btn-reject {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    color: white;
  }

  .instructor-btn-reject:hover {
    transform: translateY(-3px) scale(1.05);
    box-shadow: 0 8px 20px rgba(239,68,68,0.4);
  }

  .instructor-btn-reject::after {
    content: "✗";
    font-size: 1.1rem;
  }

  /* Enhanced Empty State for Instructors */
  .instructor-empty-state {
    text-align: center;
    padding: 80px 20px;
    background: linear-gradient(135deg, rgba(14,165,168,0.03) 0%, rgba(34,211,238,0.03) 100%);
    border-radius: 16px;
    border: 2px dashed rgba(14,165,168,0.2);
    color: var(--muted);
  }

  .instructor-empty-state-icon {
    font-size: 5rem;
    margin-bottom: 1.5rem;
    opacity: 0.6;
    animation: float 3s ease-in-out infinite;
  }

  @keyframes float {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-10px); }
  }

  .instructor-empty-state h3 {
    font-size: 1.5rem;
    margin-bottom: 0.5rem;
    color: var(--ink);
    font-weight: 700;
  }

  .container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 5vw;
  }

  footer {
    text-align: center;
    padding: 36px 5vw;
    background: linear-gradient(90deg, var(--ink), #10284f);
    font-size: 0.95rem;
    color: #e2e8f0;
    margin-top: 40px;
    border-top:1px solid rgba(255,255,255,0.06);
  }

  @keyframes gradientShift { 0% { background-position:0% 50%; } 100% { background-position:100% 50%; } }

  @media (max-width: 768px) {
    header { flex-direction: column; gap: 10px; }
    .hero h2 { font-size: 2rem; }
    .stats-grid { grid-template-columns: 1fr; }
    .action-buttons { flex-direction: column; }
    .table-container { overflow-x: scroll; }
    .instructor-action-buttons { flex-direction: column; }
    .instructor-table-container { overflow-x: scroll; }
    .instructor-section-header { flex-direction: column; text-align: center; }
    .instructor-card { padding: 20px; }
    .instructor-table-container th,
    .instructor-table-container td { padding: 12px 16px; font-size: 0.85rem; }
  }
  </style>
</head>
<body>

  <header>
    <h1>Admin Dashboard</h1>
    <a href="logout.php" class="logout-btn">Logout</a>
  </header>

  <section class="hero">
    <div class="hero-inner">
      <h2>Admin Approval Management</h2>
      <p>Review and manage company and instructor registrations. Approve or reject applications to maintain platform quality.</p>
    </div>
  </section>

  <!-- Statistics Section -->
  <section class="stats-section">
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-number"><?= $stats['pending_companies'] ?></div>
        <div class="stat-label">Pending Companies</div>
      </div>
      <div class="stat-card">
        <div class="stat-number"><?= $stats['approved_companies'] ?></div>
        <div class="stat-label">Approved Companies</div>
      </div>
      <div class="stat-card">
        <div class="stat-number"><?= $stats['pending_instructors'] ?></div>
        <div class="stat-label">Pending Instructors</div>
      </div>
      <div class="stat-card">
        <div class="stat-number"><?= $stats['approved_instructors'] ?></div>
        <div class="stat-label">Approved Instructors</div>
      </div>
      <div class="stat-card">
        <div class="stat-number"><?= $stats['total_companies'] ?></div>
        <div class="stat-label">Total Companies</div>
      </div>
      <div class="stat-card">
        <div class="stat-number"><?= $stats['total_instructors'] ?></div>
        <div class="stat-label">Total Instructors</div>
      </div>
    </div>
  </section>

  <div class="container">
    <!-- Companies Section -->
    <div class="card">
      <div class="section-header">
        <div class="section-icon">🏢</div>
        <div class="section-content">
          <h2 class="section-title">Company Approvals</h2>
          <p class="section-subtitle">Review and manage company registration requests</p>
        </div>
      </div>

      <?php 
      $companies->data_seek(0);
      $has_companies = $companies->num_rows > 0;
      ?>
      
      <?php if($has_companies): ?>
        <div class="table-container">
          <table>
            <thead>
              <tr>
                <th>Company Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Industry</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php while($c = $companies->fetch_assoc()): ?>
              <tr>
                <td><strong><?= htmlspecialchars($c['company_name']) ?></strong></td>
                <td><?= htmlspecialchars($c['email']) ?></td>
                <td><?= htmlspecialchars($c['phone']) ?></td>
                <td><?= htmlspecialchars($c['industry']) ?></td>
                <td><span class="status <?= $c['status'] ?>"><?= ucfirst($c['status']) ?></span></td>
                <td>
                  <?php if($c['status'] === 'pending'): ?>
                  <div class="action-buttons">
                    <a href="?action=approve&type=company&id=<?= $c['company_id'] ?>" class="btn btn-approve">✓ Approve</a>
                    <a href="?action=reject&type=company&id=<?= $c['company_id'] ?>" class="btn btn-reject">✗ Reject</a>
                  </div>
                  <?php else: ?>
                  <span style="color: var(--muted); font-size: 0.9rem;">Already <?= $c['status'] ?></span>
                  <?php endif; ?>
                </td>
              </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      <?php else: ?>
        <div class="empty-state">
          <div class="empty-state-icon">🏢</div>
          <h3>No Companies Found</h3>
          <p>There are no company registrations to review at this time.</p>
        </div>
      <?php endif; ?>
    </div>

    <!-- Instructors Section -->
    <div class="instructor-card">
      <div class="instructor-section-header">
        <div class="instructor-section-icon">👨‍🏫</div>
        <div class="section-content">
          <h2 class="section-title">Instructor Approvals</h2>
          <p class="section-subtitle">Review and manage instructor registration requests with enhanced details</p>
        </div>
      </div>

      <?php 
      $instructors->data_seek(0);
      $has_instructors = $instructors->num_rows > 0;
      ?>

      <?php if($has_instructors): ?>
        <div class="instructor-table-container">
          <table>
            <thead>
              <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Department</th>
                <th>University</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php while($i = $instructors->fetch_assoc()): ?>
              <tr>
                <td><span class="instructor-name"><?= htmlspecialchars($i['full_name']) ?></span></td>
                <td><a href="mailto:<?= htmlspecialchars($i['email']) ?>" class="instructor-email"><?= htmlspecialchars($i['email']) ?></a></td>
                <td><span class="instructor-phone"><?= htmlspecialchars($i['phone']) ?></span></td>
                <td><span class="instructor-department"><?= htmlspecialchars($i['department']) ?></span></td>
                <td><span class="instructor-university"><?= htmlspecialchars($i['university_name']) ?></span></td>
                <td><span class="instructor-status <?= $i['status'] ?>"><?= ucfirst($i['status']) ?></span></td>
                <td>
                  <?php if($i['status'] === 'pending'): ?>
                  <div class="instructor-action-buttons">
                    <a href="?action=approve&type=instructor&id=<?= $i['instructor_id'] ?>" class="instructor-btn instructor-btn-approve">Approve</a>
                    <a href="?action=reject&type=instructor&id=<?= $i['instructor_id'] ?>" class="instructor-btn instructor-btn-reject">Reject</a>
                  </div>
                  <?php else: ?>
                  <span style="color: var(--muted); font-size: 0.9rem; font-weight: 500;">Already <?= $i['status'] ?></span>
                  <?php endif; ?>
                </td>
              </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      <?php else: ?>
        <div class="instructor-empty-state">
          <div class="instructor-empty-state-icon">👨‍🏫</div>
          <h3>No Instructors Found</h3>
          <p>There are no instructor registrations to review at this time.</p>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <footer>
    © 2025 Admin Dashboard – All Rights Reserved.
  </footer>

  <script>
    // Topbar shadow on scroll
    const topbar = document.querySelector('header');
    const onScroll = ()=>{
      if(window.scrollY > 10) topbar.classList.add('scrolled');
      else topbar.classList.remove('scrolled');
    };
    document.addEventListener('scroll', onScroll);
    onScroll();
  </script>
</body>
</html>