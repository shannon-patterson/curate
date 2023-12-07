#include <Array.au3>
#include <File.au3>
#include <MsgBoxConstants.au3>
#include <Constants.au3>

Local $diractoryName = "C:\Users\shann\sorting\music"
Local $phpCommand = "C:\php\php-7.4\php.exe C:\Users\shann\PhpstormProjects\curate\getFiles.php"

While 1
	doCheck($diractoryName, $phpCommand)
	Sleep(10)
WEnd


Func doCheck($diractoryName, $phpCommand)
	If directoryFileCount($diractoryName) <= 1 Then
		$iPID = Run($phpCommand, $diractoryName, "", $STDOUT_CHILD)
		ProcessWaitClose($iPID)
	EndIf
EndFunc   ;==>doCheck


Func directoryFileCount($directoryName)
	Local $aFileList = _FileListToArray($directoryName, Default, $FLTA_FILES, True)

	If @error = 1 Then
		Exit
	EndIf

	If @error = 4 Then
		Return 0
	EndIf

	Return $aFileList[0]
EndFunc   ;==>directoryFileCount
