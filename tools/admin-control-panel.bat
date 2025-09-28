@echo off
setlocal enableextensions

REM Change to repo root (directory of this script)
pushd %~dp0

REM Detect Python
where python >nul 2>&1
if errorlevel 1 (
  echo Python not found in PATH. Please install Python 3 and add it to PATH.
  pause
  exit /b 1
)

REM Launch the Tkinter control panel
python tools\admin_control_panel.py

popd
endlocal
