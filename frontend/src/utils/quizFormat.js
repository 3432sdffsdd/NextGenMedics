/** Shared FCPS-style quiz format — same for .txt and .docx uploads. */

export const QUIZ_FORMAT_RULES = [
  'Title line (optional), e.g. Hip Quiz',
  'Count line (optional), e.g. 10 questions',
  'Numbered stem: 1. Question text…',
  'Optional hint: 💡 Hint: …',
  'Four options — each on its own line: A. … B. … C. … D. …',
  'Answer: A  (or ANSWER: A)',
  'Optional Rationale: …',
  'Repeat for 2., 3., … — blank line between questions is OK',
]

export const QUIZ_WORD_TIPS = [
  'Upload your Word file exactly as you write it — no changes needed.',
  'Works with .txt or .docx using the same layout below.',
]

export const QUIZ_TEMPLATE_TEXT = `Hip Quiz
10 questions
1. A patient presents with a 'waddling' gait. When standing on the left leg, the right side of the pelvis drops. Which nerve is most likely compromised?
💡 Hint: Consider which side's muscles are failing to pull the pelvis up while the opposite foot is off the ground.
A. Left superior gluteal nerve
B. Right superior gluteal nerve
C. Right inferior gluteal nerve
D. Left inferior gluteal nerve
Answer: A
Rationale: The superior gluteal nerve supplies the gluteus medius and minimus, which are responsible for stabilizing the pelvis on the stance limb.

2. During a physical examination, a patient is asked to rise from a seated position without using their arms. The patient struggles significantly with this task but can walk on level ground normally. Which muscle is most likely weakened?
💡 Hint: Identify the large muscle responsible for 'power' movements rather than just steady walking.
A. Piriformis
B. Gluteus maximus
C. Gluteus medius
D. Tensor fasciae latae
Answer: B
Rationale: The gluteus maximus is the 'power' muscle used for forceful hip extension required for climbing stairs and rising from a seated position.
`

export function downloadQuizTxtTemplate() {
  const blob = new Blob([QUIZ_TEMPLATE_TEXT], { type: 'text/plain;charset=utf-8' })
  triggerDownload(blob, 'quiz-mcq-template.txt')
}

export function downloadQuizDocxTemplate() {
  // Prefer quizService.downloadTemplate('docx') in UI components (includes auth).
  throw new Error('Use quizService.downloadTemplate("docx") or QuizFormatGuide')
}

/** @deprecated use downloadQuizTxtTemplate */
export function downloadQuizWordTemplate() {
  downloadQuizTxtTemplate()
}

function triggerDownload(blob, filename) {
  const url = URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = filename
  a.click()
  URL.revokeObjectURL(url)
}
