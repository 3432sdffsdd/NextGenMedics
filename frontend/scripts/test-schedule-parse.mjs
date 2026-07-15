import * as XLSX from 'xlsx'
import { parseMonthScheduleRows, readScheduleWorkbook } from '../src/utils/scheduleExcel.js'

const rows = [
  ['Date', 'Subject', 'Topic', 'Lecture Title', 'Start Time', 'End Time', 'Teacher', 'Meeting Link'],
  ['2026-07-01', 'Pathology', 'Nephrology', 'Lecture 003', '21:45', '23:15', 'Dr Talha ', 'https://us05web.zoom.us/j/84497226954?pwd=aoVax5b2ky9oWg2uZvm2xFE5moCeXE.1'],
  ['2026-07-02', 'Anatomy', 'Upper limb', 'Lec 001: Upper Limb Bones, Joints,Surface Anatomy', '19:30', '21:15', 'Dr Sidrah', ''],
  ['2026-07-03', 'Anatomy', 'Upper limb', 'Lec 002: Upper limb Nerves and clinical cases', '19:30', '21:15', 'Dr Sidrah', ''],
  ['2026-07-04', 'Anatomy', 'Upper limb', 'Lec 003 : Upper Limb Vasculature and brachial plexus/ Axilla', '19:00', '21:00', 'Dr Sidrah', ''],
  ['2026-07-05', '', '', '', '', '', '', ''],
  ['2026-07-06', 'Pathology', 'Nephrology', 'Lecture 004', '22:00', '23:30', 'Dr Talha ', ''],
  ['2026-07-07', 'Pharmacology', 'Nephrology', 'Lecture 005', '22:00', '23:30', 'Dr Talha ', ''],
  ['2026-07-08', 'Pathology ', 'Endocrinology', 'Lecture 006', '21:50', '22:35', 'Dr Talha ', ''],
  ['2026-07-09', 'Anatomy', 'Lower limb', 'Lec 004: Lower limb bones, joints, surface anatomy', '20:30', '22:00', 'Dr Sidrah', ''],
  ['2026-07-10', 'Anatomy', 'Lower limb', 'Lec 005: Femoral Triangle and vasculature of Lower limb', '20:30', '22:00', 'Dr Sidrah', ''],
  ['2026-07-11', 'Anatomy', 'Lower limb', 'Lec 006:  Leg, Foot & Clinical Anatomy', '20:30', '22:00', 'Dr Sidrah', ''],
  ['2026-07-12', '', '', '', '', '', '', ''],
  ['2026-07-13', 'Pathology', 'Endocrinology', 'Lecture 007', '21:30', '23:15', 'Dr Talha ', ''],
  ['2026-07-14', 'Patho + Pharma', 'Endocrinology', 'Lecture 008', '21:30', '23:15', 'Dr Talha ', ''],
  ['2026-07-15', 'Pharmacology', 'Endocrinology', 'Lecture 009', '21:30', '23:15', 'Dr Talha ', ''],
  ['2026-07-16', 'Anatomy', 'Thorax', 'Lecture 007: Thorax anatomy', '20:30', '22:00', 'Dr Sidrah', ''],
  ['2026-07-17', 'Physiology', 'Respiratory', 'Lecture 008:  Respiratory Physiology I', '20:30', '22:00', 'Dr Sidrah', ''],
  ['2026-07-18', 'Physiology', 'Respiratory', 'Lecture 009:  Respiratory Physiology II', '20:30', '22:00', 'Dr Sidrah', ''],
  ['2026-07-19', '', '', '', '', '', '', ''],
  ['2026-07-20', 'Pathology', 'CVS', 'Lecture 010', '21:30', '23:15', 'Dr Talha ', ''],
  ['2026-07-21', 'Pathology ', 'CVS', 'Lecture 011', '21:30', '23:15', 'Dr Talha ', ''],
  ['2026-07-22', 'Pathology ', 'CVS', 'Lecture 012', '21:30', '23:15', 'Dr Talha ', ''],
  ['2026-07-23', 'Anatomy', 'CVS', 'LECTURE 010: Heart and mediastinum', '20:30', '22:00', 'Dr Sidrah', ''],
  ['2026-07-24', 'Physiology', 'CVS', 'Lecture 011:  Cardiovascular Physiology I', '20:30', '22:00', 'Dr Sidrah', ''],
  ['2026-07-25', 'Physiology', 'CVS', 'Lecture 012: Cardiovascular Physiology II', '20:30', '22:00', 'Dr Sidrah', ''],
  ['2026-07-26', '', '', '', '', '', '', ''],
  ['2026-07-27', 'Pharmacology', 'CVS', 'Lecture 013', '21:30', '23:15', 'Dr Talha ', ''],
  ['2026-07-28', 'Pharmacology ', 'CVS', 'Lecture 014', '21:30', '23:15', 'Dr Talha ', ''],
  ['2026-07-29', 'Pathology', 'Rheumatology', 'Lecture 015', '21:30', '23:15', 'Dr Talha ', ''],
  ['2026-07-30', 'Anatomy', 'GIT', 'LECTURE 013: Abdomen I', '20:30', '22:00', 'Dr Sidrah', ''],
  ['2026-07-31', 'Anatomy', 'GIT', 'Lecture 014: Abdomen II', '20:30', '22:00', 'Dr Sidrah', ''],
]

const ws = XLSX.utils.aoa_to_sheet(rows)
const wb = XLSX.utils.book_new()
XLSX.utils.book_append_sheet(wb, ws, 'Schedule')
const buf = XLSX.write(wb, { type: 'array', bookType: 'xlsx' })

const jsonRows = readScheduleWorkbook(buf)
const parsed = parseMonthScheduleRows(jsonRows, '2026-07')
const parsedWrongMonth = parseMonthScheduleRows(jsonRows, '2026-08')

console.log('Total sheet rows (excl header):', jsonRows.length)
console.log('Parsed for July 2026:', parsed.length)
console.log('Parsed if August selected:', parsedWrongMonth.length)
if (parsed.length > 0) {
  console.log('First:', parsed[0])
  console.log('Last:', parsed[parsed.length - 1])
} else {
  console.log('Sample raw row 1:', jsonRows[0])
}

// Test with HH:MM:SS end times (Excel sometimes exports this)
const badTime = parseMonthScheduleRows([
  { Date: '2026-07-01', 'Lecture Title': 'Test', 'Start Time': '21:45', 'End Time': '23:15:00' },
], '2026-07')
console.log('Row with 23:15:00 end time parses:', badTime.length)
