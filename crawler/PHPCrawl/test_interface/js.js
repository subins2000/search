function show_documentation(method,path_to_documentation){f=window.open(classref_page+method+".htm","documentation","width=720, height=500, scrollbars=yes");f.focus();}
function delete_selected_setup()
{var element=document.getElementById("selected_setup_filename");var selected_value=element.options[element.selectedIndex].value;con=confirm("Do you want to delete the saved setup '"+selected_value+"' ?");if(con==true)
{setAction("delete_setup","_self");}}
function save_setupfile()
{var save_filename=document.getElementById("save_setup_filename").value;var element=document.getElementById("selected_setup_filename");for(i=0;i<element.options.length;i++)
{if(element.options[i].value==save_filename)
{con=confirm("Do you want to overwrite the setup-file '"+save_filename+"'?");if(con==true)
{setAction("save_setup","_self");}
return;}}
setAction("save_setup","_self");}
function setAction(ac,target){document.options.action.value=ac;document.options.target=target;document.options.submit();}
function showCommentDiv(div_id,flag)
{if(flag==true)
{document.getElementById(div_id).style.visibility="visible";document.getElementById("setFollowRedirects").style.visibility="hidden";document.getElementById("setFollowRedirectsTillContent").style.visibility="hidden";document.getElementById("setCookieHandling").style.visibility="hidden";document.getElementById("setAggressiveLinkExtraction").style.visibility="hidden";document.getElementById("obeyRobotsTxt").style.visibility="hidden";}
else
{document.getElementById(div_id).style.visibility="hidden";document.getElementById("setFollowRedirects").style.visibility="visible";document.getElementById("setFollowRedirectsTillContent").style.visibility="visible";document.getElementById("setCookieHandling").style.visibility="visible";document.getElementById("setAggressiveLinkExtraction").style.visibility="visible";document.getElementById("obeyRobotsTxt").style.visibility="visible";}}