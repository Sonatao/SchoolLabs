// This is one of those things i've been templating for search functions, I'll comment it out but idk if you'd like to see more Java things
// in the perifory for assignments, since this is a PHP class, but I've been picking it up on the side and wanted to use it,
// please let me know if it's not Okay!

//                                                      Submit was case sensitive. :(
document.getElementById("userSearch").addEventListener("submit", async (e) => {
  e.preventDefault();

  const name = document.getElementById("userInput").value.trim();
  const resultsDiv = document.getElementById("results");

  resultsDiv.innerHTML = "<p>Searching...</p>";


//  This if everything has gone smoothly which it has, will bring the back end to the front end, by fetching the application.
  try {
    const response = await fetch(`../CRUD.php?action=read&name=${encodeURIComponent(name)}`);
    const html = await response.text();
// In case it fails, but not an error, rather, there's just no one by that name.
    resultsDiv.innerHTML = html || "<p>No user found.</p>";
  } catch (error) {
    // If something other than there just not being anyone by that name explodes, network, server, etc.
    resultsDiv.innerHTML = `<p>Error: ${error.message}</p>`;
  }
});


/* ==============================
SEARCH FUNCTION FOR THE SEARCH BAR TO FIND PEOPLE
   ==============================*/
  //  Kept getting lost in the js, made that to differentiate. 


// This next part was interesting to do, I'm adding it to my growing list of templates, it's a script that sends front end data back
// to the server, interacting with PHP in a format they can both understand.

document.getElementById("userSearch").addEventListener("submit", async (e) => {
  e.preventDefault();

  const name = document.getElementById("userInput").value.trim();
  const resultsDiv = document.getElementById("results");

  resultsDiv.innerHTML = "<p>Searching...</p>";

  try {
    // Query PHP backend for the user(s)
    const response = await fetch(`../php/CRUD.php?action=read&name=${encodeURIComponent(name)}`);
    const users = await response.json();

    if (users.error || users.length === 0) {
      resultsDiv.innerHTML = "<p>No user found.</p>";
      return;
    }

    // Save the first user in sessionStorage for the profile page
    sessionStorage.setItem("selectedUser", JSON.stringify(users[0]));

    // Redirect to profile page
    window.location.href = "profilePage.html";

  } catch (error) {
    resultsDiv.innerHTML = `<p>Error: ${error.message}</p>`;
  }
});

