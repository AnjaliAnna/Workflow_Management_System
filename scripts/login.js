function login_form_submission()
{
        //var field = document.forms["login_form"]["uname"]["psw"].value;
        //if (field == "") {
        //  alert("All fields must be filled");
        //  return false;
        //}
        //else{
            var user_name=$('#uname').val();
            var passw=$('#psw').val();

            $.post('../api/Login.php', {loginName:user_name,password:passw}, 
                    function(data) 
                    {
                        if (data.status != 1)
                        {
                            if (data.count>0)
                            {
                                $('#msg').html(data.data[0]);
                            }
                            else
                            {
                                $('#msg').html('Unknown Error!');
                            }
                        }
                        else
                        {
                            window.location.href = data.data[0];
                        }
                    });

        //}
}