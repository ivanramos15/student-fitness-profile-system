function createRemark(userID, remarkType) {
    let remark;
    switch(remarkType.toLowerCase()){
        case "log in":
            remark = "Logged In";
            break;
        case "log out":
            remark = "Logged Out";
            break;
        case "pre-test":
            remark = "Took Pre-Test";
            break;
        case "post-test":
            remark = "Took Post-Test";
            break;
        case "changed-password":
            remark = "Changed Password";
            break;
        default:
            return;
    }
  $.ajax({
    type: 'POST',
    url: 'remarksCreationProcess.php',
    dataType: 'json',
    data: {
        'remark': remark
    },
    success: function(response) {
    },
    error: function(xhr, status, error) {
    }
  });
}