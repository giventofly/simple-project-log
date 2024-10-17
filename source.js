document.addEventListener("DOMContentLoaded", () => {
  const projectList = document.getElementById("project-list");
  let projects = Array.from(document.querySelectorAll('.project-card'));
  let sortOrder = { title: 'asc', date: 'asc' }; // Track the current sort order
  //let currentFilter = false; // Track if we are filtering by tags

  // Sorting function
  function sortProjects(key, order) {
    //currentFilter = false; // Reset filter tracking when sorting
    projects.sort((a, b) => {
      let valA = a.dataset[key];
      let valB = b.dataset[key];

      if (key === 'date') {
        valA = valA.replace(/xx/g, '01'); // Handle missing day/month in dates
        valB = valB.replace(/xx/g, '01');
      }

      if (order === 'asc') {
        return valA.localeCompare(valB);
      } else {
        return valB.localeCompare(valA);
      }
    });

    rebuildProjectList(key);
  }

  // Rebuild the project list with section separators
  function rebuildProjectList(sortKey) {
    projectList.innerHTML = ''; // Clear current projects
    let lastSection = '';

    projects.forEach(project => {
      const val = project.dataset[sortKey];
      let section = '';

        if (sortKey === 'date') {
          section = val.substr(0, 4); // Use year as section
        } else if (sortKey === 'title') {
          section = val.charAt(0).toUpperCase(); // Use first letter of title as section
        }
        else {
          section = project.dataset.date.substr(0, 4);
        }
        

        // Add section separator if the section has changed and we're not filtering by tags
        if (section !== lastSection) {
          lastSection = section;
          const separator = document.createElement('h2');
          separator.className = 'section-separator';
          separator.textContent = section;
          projectList.appendChild(separator);
        }

      projectList.appendChild(project); // Append the project card
    });
  }

  // Filter function to filter by tag
  function filterProjects() {
    const selectedTag = document.getElementById('filter-tag').value.trim();
    //currentFilter = selectedTag !== ''; // If a tag is selected, enable filtering

    projects.forEach(project => {
      const projectTags = project.getAttribute('data-tags').split(',');

      // Check if the selected tag is in the project's tags or if no tag is selected
      if (selectedTag === '' || projectTags.includes(selectedTag)) {
        project.style.display = ''; // Show project
      } else {
        project.style.display = 'none'; // Hide project
      }
    });

    rebuildProjectList('tags'); // Rebuild without section titles
  }

  // Toggle sort order and update the button arrow
  function toggleSort(key) {
    const arrowElement = document.getElementById(`${key}-arrow`);
    const currentOrder = sortOrder[key];

    if (currentOrder === 'asc') {
      sortOrder[key] = 'desc';
      arrowElement.innerHTML = '▼';
    } else {
      sortOrder[key] = 'asc';
      arrowElement.innerHTML = '▲';
    }

    sortProjects(key, sortOrder[key]);
  }

  // Slider logic for showing the previous image
  window.showPrevImage = function(button) {
    const sliderContainer = button.closest('.slider-container');
    const images = Array.from(sliderContainer.querySelectorAll('.project-image'));
    let activeIndex = images.findIndex(img => img.classList.contains('active'));

    images[activeIndex].classList.remove('active');
    activeIndex = (activeIndex - 1 + images.length) % images.length;
    images[activeIndex].classList.add('active');
  };

  // Slider logic for showing the next image
  window.showNextImage = function(button) {
    const sliderContainer = button.closest('.slider-container');
    const images = Array.from(sliderContainer.querySelectorAll('.project-image'));
    let activeIndex = images.findIndex(img => img.classList.contains('active'));

    images[activeIndex].classList.remove('active');
    activeIndex = (activeIndex + 1) % images.length;
    images[activeIndex].classList.add('active');
  };

  // Expose the functions globally
  window.toggleSort = toggleSort;
  window.filterProjects = filterProjects;

  sortProjects('date', 'desc'); // Initial sort by date
  document.getElementById('filter-tag').value = ''; // Clear the tag filter input
});