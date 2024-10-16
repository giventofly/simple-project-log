<?php
// Path to the projects.json file
$jsonFile = 'projects.json';
$htmlFile = 'index.html';

// Read the JSON file
$jsonData = file_get_contents($jsonFile);
$data = json_decode($jsonData, true);

// Check if images should be included globally
$includeImages = $data['include_images'];
$projects = $data['projects'];

// Sort projects by date (descending by default)
usort($projects, function ($a, $b) {
    return strcmp($b['date'], $a['date']);
});

// Define a set of tag colors (Dracula theme)
$colorPalette = ['#8be9fd', '#50fa7b', '#ffb86c', '#ff79c6', '#bd93f9', '#ff5555', '#f1fa8c'];
$tagColors = []; // This will store assigned colors for each tag
$colorIndex = 0;

// Assign a color to each tag dynamically based on first appearance
function getTagColor($tag, &$tagColors, &$colorIndex, $colorPalette) {
    if (!isset($tagColors[$tag])) {
        $tagColors[$tag] = $colorPalette[$colorIndex % count($colorPalette)];
        $colorIndex++;
    }
    return $tagColors[$tag];
}

// Start generating the HTML output
$htmlContent = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Project Log</title>
  <!-- Load Google Fonts and custom CSS -->
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;700&display=swap" rel="stylesheet">
  <link href="style.css" rel="stylesheet">
  <script src="script.js"></script>
</head>
<body>
  <div class="container">
    <h1 class="title">Project Log</h1>

    <!-- Sorting buttons -->
    <div class="button-group">
      <button id="sort-title" class="button" onclick="toggleSort('title')">Sort by Title <span id="title-arrow">▲</span></button>
      <button id="sort-date" class="button" onclick="toggleSort('date')">Sort by Date <span id="date-arrow">▲</span></button>
    </div>

    <!-- Filter by tag -->
    <div class="filter">
      <select id="filter-tag" class="select" onchange="filterProjects()">
        <option value="">All Tags</option>
HTML;

// Populate filter dropdown with unique tags
$uniqueTags = [];
foreach ($projects as $project) {
    foreach ($project['tags'] as $tag) {
        if (!in_array($tag, $uniqueTags)) {
            $uniqueTags[] = $tag;
            $htmlContent .= "<option value='{$tag}'>{$tag}</option>";
        }
    }
}

$htmlContent .= <<<HTML
      </select>
    </div>

    <div id="project-list">
HTML;

$currentYear = null;

// Loop through each project and generate its HTML
foreach ($projects as $project) {
    $title = htmlspecialchars($project['title']);
    $description = htmlspecialchars($project['description']);
    $notes = !empty($project['notes']) ? htmlspecialchars($project['notes']) : ''; // Add notes
    $date = htmlspecialchars($project['date']);
    $year = substr($date, 0, 4); // Extract the year

    // Display the year, and month if present
    $displayDate = $year;
    if (strpos($date, 'xx') === false) {
        $month = substr($date, 5, 2);
        if ($month !== 'xx') {
            $displayDate = "{$year}-{$month}";
        }
    }

    // Generate tags with dynamically assigned colors
    $tagsHtml = '';
    $tagsArray = []; // For the data-tags attribute
    foreach ($project['tags'] as $tag) {
        $color = getTagColor($tag, $tagColors, $colorIndex, $colorPalette);
        $tagsHtml .= "<span class='tag' style='background-color: {$color};'>{$tag}</span> ";
        $tagsArray[] = $tag; // Collect tags for data-tags attribute
    }

    // Convert tags array to a comma-separated string for the data-tags attribute
    $dataTags = implode(',', $tagsArray);

    // Include images only if `include_images` is true and the project has images
    $imageHtml = '';
    if ($includeImages && !empty($project['images'])) {
        $imageCount = count($project['images']);
        if ($imageCount > 1) {
            // Slider if multiple images are present
            $imageHtml .= '<div class="slider-container">';
            foreach ($project['images'] as $index => $image) {
                $activeClass = ($index === 0) ? 'active' : '';
                $imageHtml .= "<img src='{$image}' class='project-image {$activeClass}' alt='{$title}'>";
            }
            $imageHtml .= <<<HTML
              <button class="prev" onclick="showPrevImage(this)">&#10094;</button>
              <button class="next" onclick="showNextImage(this)">&#10095;</button>
            </div>
HTML;
        } else {
            // Single image display
            $imageHtml = "<img src='{$project['images'][0]}' class='project-image' alt='{$title}'>";
        }
    }

    // Add data attributes for sorting and filtering by title, date, and tags
    $htmlContent .= <<<HTML
      <div class="project-card" data-title="{$title}" data-date="{$date}" data-tags="{$dataTags}">
        <div class="project-content">
          <div class="image-container">{$imageHtml}</div>
          <div class="project-details">
            <h3 class="project-title">{$title}</h3>
            <p>{$displayDate}</p>
            <p>{$description}</p>
HTML;

    // Add notes if they exist
    if (!empty($notes)) {
        $htmlContent .= "<p class='notes'><strong>Notes:</strong> {$notes}</p>";
    }

    // Add tags and link to view the project
    $htmlContent .= <<<HTML
            <div class="tags">{$tagsHtml}</div>
            <a href="{$project['link']}" class="button">View Project</a>
          </div>
        </div>
      </div>
HTML;
}

$htmlContent .= <<<HTML
    </div>
  </div>

</body>
</html>
HTML;

// Write the generated HTML to the output file
file_put_contents($htmlFile, $htmlContent);

echo "HTML file generated successfully.";