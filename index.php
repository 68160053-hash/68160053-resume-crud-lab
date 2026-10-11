<?php
require_once 'config/db.php';

// ---------- Profile (starter table) ----------
$profile = null;
$sql = "SELECT * FROM profile ORDER BY id LIMIT 1";
$result = mysqli_query($conn, $sql);
if ($result && mysqli_num_rows($result) > 0) {
    $profile = mysqli_fetch_assoc($result);
}
$pid = $profile ? (int)$profile['id'] : 0;

// ---------- Education (starter table) ----------
$educations = [];
$sql = "SELECT * FROM education WHERE profile_id = $pid ORDER BY start_year DESC";
$result = mysqli_query($conn, $sql);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $educations[] = $row;
    }
}

// ---------- Skills (table: skill) ----------
$skills = [];
$sql = "SELECT * FROM skill WHERE profile_id = $pid
        ORDER BY FIELD(skill_level, 'Advanced', 'Intermediate', 'Beginner'), skill_name";
$result = mysqli_query($conn, $sql);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $skills[] = $row;
    }
}

// ---------- Training / Certificates (table: training) ----------
$trainings = [];
$sql = "SELECT * FROM training WHERE profile_id = $pid ORDER BY training_year DESC, id DESC";
$result = mysqli_query($conn, $sql);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $trainings[] = $row;
    }
}

// ---------- Projects (new section, table: project) ----------
$projects = [];
$sql = "SELECT * FROM project WHERE profile_id = $pid ORDER BY project_year DESC, id DESC";
$result = mysqli_query($conn, $sql);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $projects[] = $row;
    }
}

// skill_level (free text in the table) -> bar width (%); unknown text = 50%
$level_percent = [
    'beginner' => 40,
    'normal' => 55,
    'intermediate' => 70,
    'advanced' => 90,
    'epic' => 100,
];
?>
<!doctype html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $profile ? htmlspecialchars($profile['full_name']) : 'My Resume'; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="container">
    <p class="manage"><a href="skills.php">Manage Skills (CRUD Lab) &rarr;</a></p>
    <header class="hero card">
        <img class="avatar" src="assets/images/<?php echo $profile ? htmlspecialchars($profile['photo']) : 'student.jpg'; ?>" alt="Profile Photo">
        <div class="hero-text">
            <h1><?php echo $profile ? htmlspecialchars($profile['full_name']) : 'Your Name'; ?></h1>
            <p class="headline"><?php echo $profile ? htmlspecialchars($profile['headline']) : 'Student Resume Website'; ?></p>
            <?php if ($profile): ?>
                <ul class="contact">
                    <?php if (!empty($profile['email'])): ?>
                        <li>📧 <?php echo htmlspecialchars($profile['email']); ?></li>
                    <?php endif; ?>
                    <?php if (!empty($profile['phone'])): ?>
                        <li>📱 <?php echo htmlspecialchars($profile['phone']); ?></li>
                    <?php endif; ?>
                    <?php if (!empty($profile['address'])): ?>
                        <li>📍 <?php echo htmlspecialchars($profile['address']); ?></li>
                    <?php endif; ?>
                </ul>
            <?php endif; ?>
        </div>
    </header>

    <section class="card">
        <h2>About Me</h2>
        <p><?php echo $profile ? nl2br(htmlspecialchars($profile['about_me'])) : 'Add your profile data in phpMyAdmin.'; ?></p>
    </section>

    <section class="card">
        <h2>Education</h2>
        <?php if (count($educations) > 0): ?>
            <div class="timeline">
                <?php foreach ($educations as $edu): ?>
                    <div class="timeline-item">
                        <span class="years">
                            <?php echo htmlspecialchars($edu['start_year']); ?> -
                            <?php echo $edu['end_year'] ? htmlspecialchars($edu['end_year']) : 'Present'; ?>
                        </span>
                        <h3><?php echo htmlspecialchars($edu['degree']); ?></h3>
                        <p><?php echo htmlspecialchars($edu['institution']); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>No education data found.</p>
        <?php endif; ?>
    </section>

    <section class="card">
        <h2>Skills</h2>
        <?php if (count($skills) > 0): ?>
            <div class="skills">
                <?php foreach ($skills as $skill): ?>
                    <?php
                    $level = $skill['skill_level'];
                    $level_key = strtolower(trim($level));
                    $pct = isset($level_percent[$level_key]) ? $level_percent[$level_key] : 50;
                    $level_class = preg_replace('/[^a-z0-9]/', '', $level_key);
                    ?>
                    <div class="skill">
                        <div class="skill-head">
                            <strong><?php echo htmlspecialchars($skill['skill_name']); ?></strong>
                            <span class="level level-<?php echo $level_class; ?>"><?php echo htmlspecialchars($level); ?></span>
                        </div>
                        <div class="bar"><div class="bar-fill" style="width: <?php echo $pct; ?>%"></div></div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>No skill data found.</p>
        <?php endif; ?>
    </section>

    <section class="card">
        <h2>Projects</h2>
        <?php if (count($projects) > 0): ?>
            <div class="projects">
                <?php foreach ($projects as $project): ?>
                    <article class="project">
                        <span class="years"><?php echo htmlspecialchars($project['project_year']); ?></span>
                        <h3><?php echo htmlspecialchars($project['project_name']); ?></h3>
                        <p><?php echo htmlspecialchars($project['description']); ?></p>
                        <?php if (!empty($project['tech_stack'])): ?>
                            <p class="tech"><?php echo htmlspecialchars($project['tech_stack']); ?></p>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>No project data found.</p>
        <?php endif; ?>
    </section>

    <section class="card">
        <h2>Training / Certificates</h2>
        <?php if (count($trainings) > 0): ?>
            <?php foreach ($trainings as $training): ?>
                <div class="item">
                    <h3><?php echo htmlspecialchars($training['course_name']); ?></h3>
                    <p><?php echo htmlspecialchars($training['organization']); ?>, <?php echo htmlspecialchars($training['training_year']); ?></p>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No training data found.</p>
        <?php endif; ?>
    </section>

    <footer>
        Personal Resume Website · PHP + MySQL (mysqli) · 68160053
    </footer>
</div>
</body>
</html>
<?php mysqli_close($conn); ?>
