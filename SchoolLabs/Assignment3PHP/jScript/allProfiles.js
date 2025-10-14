document.addEventListener("DOMContentLoaded", async () => {
  const container = document.getElementById("profilesContainer");
  container.innerHTML = "<p>Loading profiles...</p>";

//   Error handlings, this try catch thing is pretty darn useful. 
  try {
    const response = await fetch("../php/CRUD.php?action=list");
    const users = await response.json();

    // Just in case they leave the search empty or if the account is deleted or something, it'll do this upon refresh or load.
    if (users.error || users.length === 0) {
      container.innerHTML = "<p>No profiles found.</p>";
      return;
    }

    // Resets the loading message so it goes away. 
    container.innerHTML = "";

    // This took more than a minute, it essentially runs through the array of 'users', in the table users, and displays them with their profile image at the top, and bio below
   users.forEach(user => {
  const card = document.createElement("div");
  card.className = "profile-card";
  card.innerHTML = `
    <img src="SchoolLabs/Assignment3PHP/uploads" alt="${user.full_Name}">
    <h2>${user.full_Name}</h2>
    <p>${user.bio || "No bio available"}</p>
  `;

    //   The redirect, it'll pull whomever you click up in a fresh page where all their information will fit in and display.
      card.addEventListener("click", () => {
        sessionStorage.setItem("selectedUser", JSON.stringify(user));
        window.location.href = "profilePage.html";
      });

      container.appendChild(card);
    });
  } catch (error) {
    container.innerHTML = `<p>Error loading profiles: ${error.message}</p>`;
  }
});
