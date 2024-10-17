
# Simple Project Log

This project is designed to automatically generate an HTML page from a `projects.json` file every time a push is made to the repository. The HTML page displays a list of projects, each with its title, description, date, tags, and images. This process is automated using GitHub Actions, which runs the PHP script (`generate.php`) to generate the HTML file.

You can also run it locally and put the generate html file in a server.

## How It Works

TLDR: edit projects.json, open generate.php, it will generate the index.html file, done!

- **`projects.json`**: This file contains all project details, such as title, description, tags, images, and notes. When a new project is added to this file, GitHub Actions automatically runs the `generate.php` script, which generates an `index.html` file containing the list of all projects.
- **`generate.php`**: This PHP script reads the `projects.json` file, generates the HTML page, and pushes the changes back to the repository.
- **GitHub Actions**: A workflow is set up to run the `generate.php` script every time a push is made to the repository.

It is ready to run, just edit the project.json and it will work, you can although make some changes to the style or the way the page is generated/interacts by changing the css or javascript.

---

## Adding a Project

To add a new project to the log, follow these steps:

1. Open the `projects.json` file in the root directory of the repository.
2. Add your new project as a new object inside the `projects` array. Each project object should have the following fields:
   - **title**: The title of the project.
   - **description**: A short description of the project.
   - **date**: The date the project was completed. You can specify partial dates using `xx` for missing month or day (e.g., `2024-xx-xx`).
   - **tags**: An array of tags associated with the project (e.g., programming languages or tools used).
   - **images**: An array of URLs for the project’s images (optional).
   - **link**: A URL link to the project (if available).
   - **notes**: Any additional notes or information about the project (optional, can be left empty).

All the text will be converted to paragraphs based on 300 characters or 3 ponctuation marks, the one that comes first.

## Site-Wide Configuration

In addition to individual projects, you can configure the entire site with the following fields in the `projects.json` file:

- **`site_title`**: The title of the site that will appear as the heading on the page.
- **`top_description`**: A description or introduction that will appear below the title (can include HTML).
- **`footer`**: A footer that will appear at the bottom of the page (can include HTML).
- **`include_images`**: A boolean value (`true` or `false`) to determine whether to include images in the project entries.

Here’s an example of a configuration with these fields:

```json
{
  "site_title": "My Project Log",
  "top_description": "<p>Welcome to my project log, showcasing my latest development projects.</p>",
  "footer": "<p>&copy; 2024 My Project Log. All rights reserved.</p>",
  "include_images": true,
  "projects": [
    {
      "title": "Sample Project",
      "description": "This is a sample project for illustration purposes.",
      "date": "2023-08-10",
      "tags": ["JavaScript", "HTML", "CSS"],
      "images": [
        "https://via.placeholder.com/150/0000FF/808080?text=Sample+Image+1",
        "https://via.placeholder.com/150/FF0000/FFFFFF?text=Sample+Image+2"
      ],
      "link": "https://example.com/sample-project",
      "notes": "This project was developed using modern JavaScript frameworks and is mobile-responsive."
    }
  ]
}
```
### Customizing the Style

The css used for the index is located on style.css (or the minified version style.min.js), you can change it to your liking or you also have the option to edit the style.scss file (don't forget you need to generate the css file after editing the scss file).

### Page interactions (javascript)

The page has a few interactions, you can click to sort by title or date, or click on the tags dropdown to filter the projects by tag. You can edit the `app.min.js` file (it is a minified and transpiled version) or use the source.js file to make your changes and run in the terminal 'gulp minjs'.

You will need to do `npm install` and then to update do `gulp minjs` to generate the minified version of the javascript file.

---

### Sample Project Entry

Here’s an example of what a project entry in `projects.json` might look like:

```json
{
  "title": "Sample Project",
  "description": "This is a sample project for illustration purposes.",
  "date": "2023-08-10",
  "tags": ["JavaScript", "HTML", "CSS"],
  "images": [
    "https://via.placeholder.com/150/0000FF/808080?text=Sample+Image+1",
    "https://via.placeholder.com/150/FF0000/FFFFFF?text=Sample+Image+2"
  ],
  "link": "https://example.com/sample-project",
  "notes": "This project was developed using modern JavaScript frameworks and is mobile-responsive."
}
```

## GitHub Actions Configuration

The process of generating the `index.html` file is automated using GitHub Actions. Here’s how it’s set up:

### Workflow Setup (`.github/workflows/generate-html.yml`)

A GitHub Actions workflow file (`generate-html.yml`) is configured to automatically run every time a push is made to the repository. It triggers the `generate.php` script, which reads the `projects.json` file, generates an `index.html` file, and pushes the changes back to the repository.

Here’s how the workflow is defined:

```yaml
name: Generate HTML on Push

on:
  push:
    branches:
      - main  # Trigger on pushes to the main branch

jobs:
  build:
    runs-on: ubuntu-latest  # Use the latest Ubuntu environment

    steps:
    - name: Checkout repository
      uses: actions/checkout@v2  # Check out the repository

    - name: Set up PHP
      uses: shivammathur/setup-php@v2  # Set up PHP
      with:
        php-version: '8.2'  # Adjust PHP version as needed

    - name: Run generate.php
      run: php generate.php  # Run the PHP script to generate the HTML file

    - name: Commit generated HTML
      run: |
        git config --global user.name "GitHub Actions"
        git config --global user.email "actions@github.com"
        git add -f index.html  # Force-add the generated HTML
        git commit -m "Automated generation of HTML from generate.php"
        git push

```

Steps to Set Up

	1.	Ensure your repository has a .github/workflows/generate-html.yml file (as shown above).
	2.	Ensure the generate.php script is located in the root directory of your repository.
	3.	Ensure your projects.json file is correctly formatted with the necessary project details.
	4.	When you push new changes (such as adding a new project), GitHub Actions will automatically run, and the index.html file will be updated with the latest project details.

## Enable GitHub Pages

1. In your repository, go to **Settings** > **Pages**.
2. Under **Source**, choose the branch where your HTML is being generated (typically `main`).
3. Save the settings.

Now, GitHub Pages will serve your site on the custom domain you’ve configured.


### Setting Up a Custom Domain (CNAME)

If you want to use a custom domain to serve your GitHub Pages (for example, `project-log.josemoreira.pt`), follow these steps:

### Step 1: Set up CNAME

1. In the root of your repository, create a file named `CNAME`.
2. Add your custom domain to the `CNAME` file. For example: project-log.josemoreira.pt
3. Commit the `CNAME` file to your repository.

### Step 2: Configure DNS

You need to configure your domain’s DNS to point to GitHub Pages. Add the following DNS records at your domain registrar’s settings:

- **Type**: `CNAME`
- **Name**: `project-log` (or leave it empty to point the root domain)
- **Target**: `username.github.io` (replace `username` with your GitHub username)

### Enable GitHub Pages

1. In your repository, go to **Settings** > **Pages**.
2. Under **Custom domain**, add your domain (e.g., `project-log.josemoreira.pt`).
3. Save the settings.

