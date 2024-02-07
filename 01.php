<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Link Generator</title>
</head>
<body>

  <!-- Input field to enter user ID -->
  <label for="userId">Enter User ID: </label>
  <input type="text" id="userId">
  
  <!-- Button to generate link -->
  <button onclick="generateLink()">Generate Link</button>
  
  <!-- Display generated link -->
  <p id="generatedLink"></p>

  <script>
    // Function to generate the link with the user ID
    function generateLink() {
      // Get the user ID from the input field
      var userId = document.getElementById("userId").value;
      
      // Check if the user ID is not empty
      if (userId.trim() !== "") {
        // Generate the link using the user ID
        var link = "https://example.com/userprofile?id=" + userId;
        
        // Display the generated link
        document.getElementById("generatedLink").innerHTML = "Generated Link: <a href='" + link + "'>" + link + "</a>";
      } else {
        // Display an error message if the user ID is empty
        document.getElementById("generatedLink").innerHTML = "Please enter a User ID.";
      }
    }
  </script>

</body>
</html>
