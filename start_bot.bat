@echo off
title HRM Bot Service
echo Dang khoi dong AI Chatbot Service...
cd /d "%~dp0bot_service"
python -m uvicorn app:app --host 127.0.0.1 --port 8001 --reload
pause
