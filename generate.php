<?php
// Path to the projects.json file
$jsonFile = 'projects.json';
$htmlFile = 'index.html';

// Read the JSON file
$jsonData = file_get_contents($jsonFile);
$data = json_decode($jsonData, true);

// Get the site-wide configuration
$siteTitle = $data['site_title'] ?? 'My Project Log';  // Default title
$topDescription = $data['top_description'] ? '<div class="top-desc">'.$data['top_description'].'</div>' : '';      // Default empty if not provided
$footer = $data['footer'] ?? '';                       // Default empty if not provided

// Check if images should be included globally
$includeImages = $data['include_images'];
$projects = $data['projects'];

// Sort projects by date (descending by default)
usort($projects, function ($a, $b) {
    return strcmp($b['date'], $a['date']);
});

// Define a set of tag colors (Dracula theme)
$colorPalette = ['#8be9fd', '#50fa7b', '#ffb86c', '#ff79c6', '#bd93f9', '#ff5555', '#f1fa8c','#6fcf97', '#f299c1', '#ffcc5c', '#9b51e0', '#2d9cdb', '#f2a541', '#27ae60', '#eb5757', '#bb6bd9', '#56ccf2'];
$tagColors = []; // This will store assigned colors for each tag
$colorIndex = 0;

// Assign a color to each tag dynamically based on first appearance (case-insensitive)
function get_tag_color($tag, &$tagColors, &$colorIndex, $colorPalette) {
  // Normalize tag to lowercase for case-insensitive comparison
  $lowerTag = strtolower($tag);

  // Check if the lowercase version of the tag already has a color assigned
  if (!isset($tagColors[$lowerTag])) {
      $tagColors[$lowerTag] = $colorPalette[$colorIndex % count($colorPalette)];
      $colorIndex++;
  }

  // Return the color for the lowercase tag
  return $tagColors[$lowerTag];
}

// generates paragraphs from text
function create_paragraphs($text) {
  // Sanitize the input using htmlspecialchars
  $text = htmlspecialchars($text);

  // Define a regular expression to match punctuation (., !, ?, ;). Group similar punctuations
  $punctuationPattern = '/([.!?;])+/';
  
  $paragraphs = [];
  $buffer = '';
  $punctuationCount = 0;
  
  // Loop through the text character by character
  for ($i = 0; $i < strlen($text); $i++) {
      $char = $text[$i];
      $buffer .= $char;

      // Check if the character is a punctuation using regex and only count it once for sequences
      if (preg_match($punctuationPattern, $char)) {
          // Ensure the next character is not part of the same punctuation sequence
          if ($i === strlen($text) - 1 || !preg_match($punctuationPattern, $text[$i + 1])) {
              $punctuationCount++;
          }
      }

      // Create a paragraph if buffer has reached 300 characters or 2 punctuation marks
      if (strlen($buffer) >= 300 || $punctuationCount >= 3) {
          $paragraphs[] = "<p>{$buffer}</p>";
          $buffer = '';
          $punctuationCount = 0;
      }
  }

  // Append any remaining text as the last paragraph
  if (!empty($buffer)) {
      $paragraphs[] = "<p>{$buffer}</p>";
  }

  // Return the joined paragraphs
  return implode("\n", $paragraphs);
}



// Start generating the HTML output
$html_content = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{$siteTitle}</title>
  <!-- Load Google Fonts and custom CSS -->
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap');
  </style>
  <link href="style.min.css" rel="stylesheet">
  <script src="app.min.js?v11"></script>
</head>
<body>
  <div class="container withmargins">
    <h1 class="title">{$siteTitle}</h1>
    {$topDescription}

    <div class="filters">
      <!-- Sorting buttons -->
      <button id="sort-title" class="item button" onclick="toggleSort('title')">Sort by Title <span id="title-arrow">▲</span></button>
      <button id="sort-date" class="item button" onclick="toggleSort('date')">Sort by Date <span id="date-arrow">▲</span></button>
      <!-- Filter by tag -->
      <div class="filter item">
        <select id="filter-tag" class="select" onchange="filterProjects()">
          <option value="">All Tags</option>
HTML;

// Populate filter dropdown with unique tags (case-insensitive and sorted alphabetically)
$uniqueTags = [];

// Collect all unique tags
foreach ($projects as $project) {
    foreach ($project['tags'] as $tag) {
        // Normalize the tag to lowercase for case-insensitive comparison
        $lowerTag = strtolower($tag);

        // Check if the lowercase version of the tag is already in the unique tags array
        if (!in_array($lowerTag, array_map('strtolower', $uniqueTags))) {
            // Add the original tag (not normalized) for display purposes
            $uniqueTags[] = $tag;
        }
    }
}

// Sort the tags alphabetically (case-insensitive)
usort($uniqueTags, 'strcasecmp');

// Add the sorted tags to the dropdown
foreach ($uniqueTags as $tag) {
    $lower_case_tag = strtolower($tag);
    $html_content .= "<option value='{$lower_case_tag}'>{$tag}</option>";
}

$html_content .= <<<HTML
        </select>
      </div>
    </div>
    <div id="project-list">
HTML;

$currentYear = null;

// Loop through each project and generate its HTML
foreach ($projects as $project) {
    $title = htmlspecialchars($project['title']);
    $description = create_paragraphs($project['description']);
    $notes = !empty($project['notes']) ? create_paragraphs($project['notes']) : ''; // Add notes
    $date = htmlspecialchars($project['date']);
    $year = substr($date, 0, 4); // Extract the year

    // Display the year, and month if present
    $displayDate = $year;
    $month = substr($date, 5, 2);
    if ($month !== 'xx') {
        $displayDate = "{$year}-{$month}";
    }
    

    // Generate tags with dynamically assigned colors
    $tagsHtml = '';
    $tagsArray = []; // For the data-tags attribute
    foreach ($project['tags'] as $tag) {
      // Get the color for the tag (case-insensitive)
      $color = get_tag_color($tag, $tagColors, $colorIndex, $colorPalette);
      // Generate the tag HTML with the original case but case-insensitive color assignment
      $tagsHtml .= "<span class='tag' style='background-color: {$color};'>{$tag}</span> ";
      // Collect the original tag (not normalized) for the data-tags attribute
      $tagsArray[] = strtolower($tag);
  }
  

    // Convert tags array to a comma-separated string for the data-tags attribute
    $dataTags = implode(',', $tagsArray);

    // Include images only if `include_images` is true and the project has images
    $image_html = '';
    //$image_html .= '<div class="image-container">';
    if ($includeImages && !empty($project['images'])) {
        $imageCount = count($project['images']);
        if ($imageCount > 1) {
            // Slider if multiple images are present
            $image_html .= '<div class="slider-container">';
            foreach ($project['images'] as $index => $image) {
                $activeClass = ($index === 0) ? 'active' : '';
                $image_html .= "<img src='{$image}' class='project-image {$activeClass}' alt='{$title}'>";
            }
            $image_html .= <<<HTML
              <button class="prev" onclick="showPrevImage(this)">&#10094;</button>
              <button class="next" onclick="showNextImage(this)">&#10095;</button>
            </div>
HTML;
        } else {
            // Single image display
            $image_html = "<img src='{$project['images'][0]}' class='project-image' alt='{$title}'>";
        }
    }
    //$image_html .= '</div>';


    // Add data attributes for sorting and filtering by title, date, and tags
    $html_content .= <<<HTML
      <div class="project-card" data-title="{$title}" data-date="{$date}" data-tags="{$dataTags}">
        <div class="project-content">
          {$image_html}
          <div class="project-details">
            <h3 class="project-title">{$title}</h3>
            <div class="date">{$displayDate}</div>
            <div class="description">{$description}</div>
HTML;

    // Add notes if they exist
    if (!empty($notes)) {
        $html_content .= "<div class='notes'>{$notes}</div>";
    }

    //check if link exists
    $html_link = '';
    if (!empty($project['link'])) {
        $html_link .= "<a href='{$project['link']}' class='project-link button'>View Project</a>";
    }

    // Add tags and link to view the project
    $html_content .= <<<HTML
            </div>
        </div>
        <div class="tags">{$tagsHtml}</div>
        {$html_link}
      </div>
  </div>
HTML;
}

// Add footer
$html_content .= <<<HTML
    </div>
    <footer>
      <div class="withmargins footer">
        {$footer}
      </div>
    </footer>
  </div>
</body>
</html>
HTML;

// Write the generated HTML to the output file
file_put_contents($htmlFile, $html_content);

echo "HTML file generated successfully.";