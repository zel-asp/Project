//Search functionality
document.addEventListener('DOMContentLoaded', function () {
  const searchInput = document.querySelector('.Search');
  const searchButton = document.querySelector('.btn-dark');
  const motorParts = document.querySelectorAll('#MotorParts .card');

  function filterParts() {
    const searchTerm = searchInput.value.toLowerCase();

    motorParts.forEach(part => {
      const title = part.querySelector('.card-title').textContent.toLowerCase();
      const description = part.querySelector('.card-text').textContent.toLowerCase();

      if (title.includes(searchTerm) || description.includes(searchTerm)) {
        part.parentElement.style.display = 'block';
      } else {
        part.parentElement.style.display = 'none';
      }
    });
  }

  // Add event listeners for both button click and Enter key
  searchInput.addEventListener('input', filterParts);

  searchButton.addEventListener('click', filterParts);
  searchInput.addEventListener('keyup', function (e) {
    if (e.key === 'Enter') {
      filterParts();
    }
  });
});



//for admin dashboard

// Toggle sidebar on mobile
document.getElementById('menuToggle').addEventListener('click', function () {
  document.getElementById('sidebar').classList.toggle('active');
});

document.getElementById('closeBtn').addEventListener('click', function () {
  document.getElementById('sidebar').classList.remove('active');
});

// Navigation between sections
document.querySelectorAll('.menu-item').forEach(item => {
  item.addEventListener('click', function (e) {
    e.preventDefault();

    // Get the target section from data attribute
    const targetSection = this.getAttribute('data-section');

    // Remove active class from all menu items and sections
    document.querySelectorAll('.menu-item').forEach(menuItem => {
      menuItem.classList.remove('active');
    });
    document.querySelectorAll('.section').forEach(section => {
      section.classList.remove('active');
    });

    // Add active class to clicked menu item and target section
    this.classList.add('active');
    document.getElementById(`${targetSection}-section`).classList.add('active');

    // Update page title
    document.getElementById('pageTitle').textContent = this.querySelector('span').textContent;
  });
});

function Add_product() {
  document.getElementById('addProductBtn').addEventListener('click', function () {
    document.getElementById('Add_product').style.display = "block";
    document.getElementById('productList').style.display = "none";
  });
}
function AddcancelProductForm() {
  document.getElementById('cancelProductForm').addEventListener('click', function () {
    document.getElementById('Add_product').style.display = "none";
    document.getElementById('productList').style.display = "block";
  });
}


Add_product();
AddcancelProductForm();



