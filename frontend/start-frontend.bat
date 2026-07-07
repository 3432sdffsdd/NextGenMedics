@echo off
title NextGen Medics Frontend
cd /d d:\LMS\frontend
echo Starting frontend at http://localhost:5173
echo Network URL will be shown below (use that on phone/tablet).
npm run dev -- --host
