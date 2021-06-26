function add_user()
{
    var user_id=0;
    var user_name=$('#txt_name').val();
    var email_id=$('#txt_email').val();
    var passw=$('#txt_psw').val();
    var is_admin=$('chk_isadmin').val();

    $.post('../api/Adduser.php', {loginName:user_name,password:passw,EmailId:email_id,IsAdmin:is_admin}, 
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
}