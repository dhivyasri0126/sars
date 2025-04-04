document.getElementById("signup-form").addEventListener("submit", function(event) {
    event.preventDefault(); // Prevent the form from refreshing the page
  
    // Get form data
    const name = document.getElementById("name").value;
    const dob = document.getElementById("dob").value;
    const branch = document.getElementById("branch").value;
    const batch = document.getElementById("batch").value;
    const department = document.getElementById("department").value;
    const email = document.getElementById("email").value;
    const password = document.getElementById("password").value;
    const gender = document.querySelector('input[name="gender"]:checked').value;
  
    // For demonstration, log the data to the console
    console.log({
      name,
      dob,
      branch,
      batch,
      department,
      email,
      password,
      gender
    });
  
    alert("Signup successful!");
  });
