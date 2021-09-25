# ken-batcher-memorial
Memorial application in honor of Kenneth Batcher

### Uploading code via Git Gui
1. From inside the local folder containing the files you want to upload, open Git Gui.
	1. Git Gui can be opened by right clicking and selecting Git Gui.
1. Git Gui should show a list of "Unstaged Changes".
1. Click on "Stage Changed". If you don't want a particular file uploaded, click on it to move it back to being unstaged.
1. Type in a "Commit Message".
1. Click "Commit"
1. Click "Push"
1. A window pops up. The default options should be fine. Click "Push".
1. Type in the passphrase for your key. (If you don't have an RSA key in your Git Hub account, you may wish to add one.)
1. Check GitHub and verfiy your changes were uploaded.

### Downloading code from Git using Git Bash
1. It is a good idea to do this every time before you start editing files locally.
1. From inside the local folder containing the files you want to upload, open Git Bash.
	1. Git Bash can be opened by right clicking and selecting Git Bash.
1. Type in the following command: git pull git@github.com:itseullman/ken-batcher-memorial.git main
	1. If there are more branches in addition to "main", you may wish to download from one of those.
	1. In which case, you would change "main" to the name of the branch in question.
1. Type in the passphrase for your key. (If you don't have an RSA key in your Git Hub account, you may wish to add one.)
1. Open one of the files (or the file, if only one) which were downloaded to verify that it has been updated.


