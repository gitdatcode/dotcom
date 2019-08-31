let sa = require("superagent");

$(document).ready( () => {
  
  //create object for POST to dobot
  let dobot = {
    'event': {
      'type': "signup"
    }
  };

  let subscribe_button = $("#mc-embedded-subscribe"),
  fname = $("#mce-FNAME"),
  lname = $("#mce-LNAME"),
  email = $("#mce-EMAIL"),
  social = $('#mce-SOCIAL'),
  how = $('#mce-MMERGE5'),
  message = $('#message'),
  required = [fname, lname, email, social, how];

  subscribe_button.click ( () => {
    //get the dobot values from the form
    dobot.event.first_name = fname.val();
    dobot.event.last_name = lname.val();
    dobot.event.email = email.val();
    dobot.event.social = social.val();
    dobot.event.how = how.val();

    var can_submit = true;

    for(var i = 0, l = required.length; i < l; i++){
      var field = required[i];

      if(!field.val()){
        can_submit = false;
        // do something to the field here
        field.addClass(' required');
      }
    }
      //POST to the dobot app
    if(can_submit) {
      for(var i = 0, l = required.length; i < l; i++){
        field.removeClass('required');
      }

      sa.post('http://45.55.90.231:8921')
      .set('Content-Type', 'application/json')
      .send(JSON.stringify(dobot))
      .end( (err, res) => {
        console.log(res);
        if (err) {
        console.log('error from dobot: ', err);
        }
        else {
          message.text("Great! We got it. You'll hear from us soon.");
          message.removeClass("red");
          message.addClass(" green");
        }
      });
    }
    else {
      message.text("Missing some fields!");
      message.addClass(' red');
    }
  });
});