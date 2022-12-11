

//Let's create our own validation message
//Begin by setting up some helpful functions
// CREATE ELEMENT + SET CONTENTS
function subscriptionMessage(status = "none", formURL = "") {
  console.log ("subscriptionMessage running");
  //Wait until the page exists.

    console.log (formURL);
    const mbValidationMessage = document.querySelector(".rwmb-confirmation");
    const message = document.createElement("div");

    //check that MB is displaying a confirmation message
    if ( mbValidationMessage ) {
      console.log ("subscriptionMessage running and cookie exists");
      if (status == 1) {
        message.className = "rwmb-confirmation";
        message.innerHTML = "Thanks for subscribing to our newsletter!";

      } else if (status == 0 && formURL !== "") {
        message.className = "rwmb-error";
        message.innerHTML = "Uh oh, we could not subscribe you to our newsletter. Please try again <a href='"+formURL+"' target='_blank'>here!</a>";

      } else if (status == 0) {
        message.className = "rwmb-error";
        message.innerHTML = "Uh oh, we could not subscribe you to our newsletter. Please try again!";

      } else {
        return;
      }

      mbValidationMessage.parentNode.insertBefore(message, mbValidationMessage.nextSibling);
      eraseCookie('mailchimpvalidation');
  }
}


function getCookie(name) {
    var nameEQ = name + "=";
    var ca = document.cookie.split(';');
    for(var i=0;i < ca.length;i++) {
        var c = ca[i];
        while (c.charAt(0)==' ') c = c.substring(1,c.length);
        if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
    }
    return null;
}

function eraseCookie(name) {
    document.cookie = name +'=; Path=/; Expires=Thu, 01 Jan 1970 00:00:01 GMT; SameSite=Lax';
}
