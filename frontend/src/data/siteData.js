// Central content data for NextGen Medics LMS
import {
  FiVideo, FiHelpCircle, FiHeadphones, FiBookOpen,
  FiClipboard, FiBarChart2,
} from 'react-icons/fi'

export const navMenu = [
  { label: 'Home', to: '/' },
  {
    label: 'Courses',
    mega: true,
    columns: [
      {
        heading: 'Exam Tracks',
        items: [
          { label: 'FCPS Part 1', to: '/courses/fcps-part-1', desc: 'Complete FCPS Part 1 preparation' },
          { label: 'Upcoming Courses', to: '/courses/upcoming', desc: 'New batches launching soon' },
        ],
      },
    ],
  },
  {
    label: 'Resources',
    mega: true,
    columns: [
      {
        heading: 'Study Material',
        items: [
          { label: 'Free Videos', to: '/resources/free-videos', desc: 'Sample lectures & tutorials' },
          { label: 'Free Notes', to: '/resources/free-notes', desc: 'Downloadable revision notes' },
          { label: 'E-Books', to: '/resources/ebooks', desc: 'Curated medical e-books' },
        ],
      },
      {
        heading: 'Exam Prep',
        items: [
          { label: 'FCPS Syllabus', to: '/resources/syllabus', desc: 'Official CPSP syllabus & structure' },
          { label: 'Past Papers', to: '/resources/past-papers', desc: 'Years of solved papers' },
        ],
      },
    ],
  },
  {
    label: 'Community',
    mega: true,
    columns: [
      {
        heading: 'Connect',
        items: [
          { label: 'Our Mentors', to: '/community/mentors', desc: 'Meet expert faculty' },
          { label: 'Student Testimonials', to: '/community/testimonials', desc: 'Real student reviews' },
        ],
      },
    ],
  },
  {
    label: 'Announcements',
    mega: true,
    columns: [
      {
        heading: 'Stay Updated',
        items: [
          { label: 'Latest Updates', to: '/announcements/latest', desc: 'Platform news & notices' },
          { label: 'Upcoming Batches', to: '/announcements/batches', desc: 'Enrollment schedules' },
        ],
      },
    ],
  },
  {
    label: 'Contact',
    mega: true,
    columns: [
      {
        heading: 'Get In Touch',
        items: [
          { label: 'Contact Form', to: '/contact', desc: 'Send us a message' },
          { label: 'Help Center', to: '/help-center', desc: 'FAQs & guides' },
        ],
      },
    ],
  },
]

export const heroFeatures = [
  'Live Interactive Lectures',
  'Exam Focused Regular Assessment',
  'Past Papers & Explanations',
  'Expert Faculty & Mentorship',
  'Mock Exams',
  'Performance Analytics',
]

export const keyHighlights = [
  'Live Interactive Lectures',
  'Exam Focused Regular Assessment',
  'Past Papers & Explanations',
  'Comprehensive Syllabus Coverage',
  'Expert Faculty & Mentorship',
]

export const stats = [
  { value: 5000, suffix: '+', label: 'Students Trained' },
  { value: 500, suffix: '+', label: 'Video Lectures' },
  { value: 50, suffix: '+', label: 'Expert Instructors' },
  { value: 95, suffix: '%', label: 'Success Rate' },
]

export const features = [
  {
    icon: FiVideo,
    title: 'Live Interactive Lectures',
    desc: 'Engaging live classes with direct access to expert instructors. Ask questions, clarify doubts and learn better.',
    span: 'lg:col-span-2',
  },
  {
    icon: FiHelpCircle,
    title: 'Question Bank',
    desc: 'Access engaging interactive quizzes and regular assessments in one place. Strengthen your understanding and track progress.',
  },
  {
    icon: FiBookOpen,
    title: 'E-Books & Notes',
    desc: 'Access notes, study guides and resources from top educators, all in one place. Fully updated materials anytime.',
  },
  {
    icon: FiClipboard,
    title: 'Mock Exams',
    desc: 'Attempt real exam-like mocks with detailed performance analysis to identify your strengths and weaknesses.',
  },
  {
    icon: FiHeadphones,
    title: 'Dedicated Support',
    desc: 'Get prompt assistance whenever you need help. Our support team is committed to your learning journey and success.',
    span: 'lg:col-span-2',
  },
  {
    icon: FiBarChart2,
    title: 'Performance Analytics',
    desc: 'Track your performance with advanced analytics and visual reports to improve your preparation strategy.',
  },
]

export const featuredCourse = {
  badge: 'Featured Course',
  title: 'FCPS Part 1 Preparation Course',
  subtitle: 'For October 2026 Attempt',
  price: 'Rs 8,000',
  originalPrice: 'Rs 10,000',
  duration: '12 Weeks',
  highlights: [
    'Live Interactive Lectures',
    'Exam Focused Regular Assessment',
    'Past Papers & Explanations',
    'Comprehensive Syllabus Coverage',
    'Expert Faculty & Mentorship',
  ],
}

export const WHATSAPP_NUMBER = '923218902931'
export const whatsappLink = (message) =>
  `https://wa.me/${WHATSAPP_NUMBER}?text=${encodeURIComponent(message)}`

export const syllabus = {
  specialties: [
    { num: 1, name: 'Medicine And Allied', file: '#' },
    { num: 2, name: 'Surgery And Allied', file: '#' },
  ],
  paper1: [
    {
      title: 'Behavioral Science & Medical Ethics',
      desc: 'Fundamental principles, professional conduct, and ethical considerations in medical practice.',
    },
    {
      title: 'Research & Biostatistics',
      desc: 'Introduction to research methodology, epidemiological concepts, and basic statistical principles.',
    },
    {
      title: 'Pathology & Microbiology',
      desc: 'Medical genetics, immunology, nutritional disorders, laboratory investigations, and the study of bacteria, viruses, and parasites.',
    },
    {
      title: 'Physiology, Pharmacology & Biochemistry',
      desc: 'Body temperature regulation, renal and cardiovascular function, blood groups and coagulation, autonomic nervous system, drug action and metabolism.',
    },
  ],
  paper2: [
    'Anatomy, Histology & Embryology (specialty-focused)',
    'Physiology & Biochemistry',
    'Pathology, Microbiology & Immunology',
    'Pharmacology (clinical emphasis)',
  ],
}

export const courseDetail = {
  whatYouGet: [
    'Complete Syllabus Coverage',
    'Recorded Lectures',
    'Live Lectures',
    'Past Papers Discussion',
    'Focused Preparation Better Results',
    'Regular Assessments & Tests',
  ],
  description: [
    'Our FCPS 1 preparation course is designed to build strong conceptual understanding and exam-focused knowledge.',
    'We cover high-yield theory and convert important concepts into MCQ-based learning.',
    'Regular assessments, past paper discussions, and guided practice help improve exam strategy.',
    'With structured mentoring and focused preparation, students gain confidence to clear FCPS Part 1.',
  ],
}

export const mentors = [
  {
    name: 'Dr Sidrah Shahid Khan',
    role: 'Mentor / Facilitator',
    qualification: 'MBBS / Certified Medical Educationist',
    experience: 'Passionate educator with 8+ years of experience helping students achieve academic excellence through innovative teaching methods.',
    specialization: 'Anatomy, Physiology & Biochemistry',
    photo: '/mentors/dr-sidrah.jpg',
    tagline: 'Master the Concepts. Conquer the MCQs. Clear FCPS Part-I.',
    bio: [
      'Dr. Sidrah Khan is a Certified Medical Educationist and experienced medical educator with a passion for helping postgraduate trainees succeed in high-stakes professional examinations. Over the years, she has guided medical students and graduates through structured learning strategies, concept-based teaching, and exam-focused preparation techniques designed to maximize performance in competitive assessments.',
      'Her approach to FCPS Part-I preparation combines deep conceptual understanding with proven exam strategies. Through high-yield lectures, active recall methods, integrated learning, MCQ-based discussions, and systematic revision plans, she helps trainees master Anatomy, Physiology, and Biochemistry with confidence.',
      'Dr. Sidrah\u2019s teaching style is structured, student-centered, and highly exam-focused, with a strong emphasis on conceptual clarity. She incorporates interactive discussions, real-life clinical correlations, active recall techniques, and regular formative assessments to ensure trainees develop both knowledge and confidence.',
      'What sets her teaching apart is the integration of modern educational strategies with extensive exam-focused preparation. Her lessons combine active recall, spaced repetition, digital learning tools, and past paper practice from the very beginning, ensuring trainees are continuously preparing for the examination rather than leaving revision until the end.',
      'This course is designed not only to teach content but also to develop effective study habits, critical thinking abilities, and exam-taking skills. Her goal is to simplify complex concepts, eliminate information overload, and provide a clear roadmap that empowers trainees to achieve success in the FCPS Part-I examination.',
    ],
  },
  {
    name: 'Dr Muhammad Talha Nazeer',
    role: 'Mentor / Facilitator',
    qualification: 'MBBS / FCPS Part 1 (Surgery), USMLE Step 1',
    experience: 'Dedicated educator with over 8 years of experience guiding students toward academic success through effective and engaging teaching strategies.',
    specialization: 'Surgery & USMLE Step 1',
    photo: '/mentors/dr-talha.jpeg',
    tagline: 'Where Smart Study Meets Exam Success.',
    bio: [
      'Dr. Muhammad Talha Nazeer is a dedicated medical educator and mentor with a strong academic background and extensive experience guiding students through competitive medical examinations. A graduate of Dow University of Health Sciences (Class of 2024), he has successfully cleared both FCPS Part-I (Surgery) and USMLE Step 1, giving him firsthand insight into the strategies required to excel in high-stakes examinations.',
      'With over eight years of teaching experience, Dr. Talha has helped numerous medical students build strong foundational knowledge, improve their problem-solving abilities, and develop effective exam-taking skills. His teaching philosophy focuses on simplifying complex concepts through structured explanations, clinical correlations, active learning techniques, and high-yield exam-focused discussions.',
      'Known for his approachable teaching style and ability to break down difficult topics into manageable concepts, he creates an engaging learning environment that encourages understanding rather than rote memorization. His sessions integrate conceptual learning, MCQ-based practice, and evidence-based study strategies to help students achieve their academic and professional goals.',
    ],
  },
]

export const testimonials = [
  {
    name: 'Mina',
    course: 'Student',
    rating: 5,
    gender: 'female',
    instructor: 'Dr Sidrah Shahid',
    review: 'Online classes with mentor Sidrah Khan were perfect and very helpful. She explains everything in an understandable way with step-by-step examples, which made difficult topics much easier for me. My problem-solving ability improved a lot because of her. I had a wonderful experience and would highly recommend her.',
  },
  {
    name: 'Shafay',
    course: 'Student',
    rating: 5,
    gender: 'male',
    instructor: 'Dr Sidrah Shahid',
    review: 'At the beginning, medical exam prep felt confusing, but over time it became much easier. Mam Sidra played a big role in that—she explained everything patiently and clearly. No matter how many times I asked questions, she always made sure I understood.',
  },
  {
    name: 'Basma',
    course: 'Student',
    rating: 5,
    gender: 'female',
    instructor: 'Dr Sidrah Shahid',
    review: 'My experience in her classes has been very positive. The lessons are well structured and easy to follow. Concepts are explained clearly with relevant examples, making learning more engaging and effective.',
  },
  {
    name: 'Anonymous',
    course: 'Student',
    rating: 5,
    gender: 'male',
    instructor: 'Dr Muhammad Talha Nazeer',
    review: 'The biochem classes are like going through a summary of Lippincott, or maybe better phrased; reading Lippincott becomes easier after taking the biochem classes because you guys actually explain in the same way how it is explained in the book, the sequence and examples etc. Dr Talha makes the biochem faculty goated. Moreover, the best thing Dr Talha does is that he uses his writing pad and projector (this helps everyone to see the boardwork properly, majority of the class loves this because most of us have done A levels and we are habitual of this teaching method). Also the questions of notebook LM in the practical room were one of the best techniques.',
  },
  {
    name: 'Anonymous',
    course: 'Student',
    rating: 5,
    gender: 'male',
    instructor: 'Dr Muhammad Talha Nazeer',
    review: 'The greatest strength of Dr Talha teaching style in my opinion has to be how naturally engaging and enjoyable his classes are. His humor, wittiness & especially the way he involves everyone makes the class and the concepts fun instead of feeling forced. One can also tell that he loves what he does and I think that makes a HUGE difference. The best part is that I actually end up understanding every concept that he teaches without feeling overwhelmed.',
  },
  {
    name: 'Anonymous',
    course: 'Student',
    rating: 5,
    gender: 'male',
    instructor: 'Dr Muhammad Talha Nazeer',
    review: 'Before opting for this course with Dr Talha, I was unsure of how it was going to be but once we got through the middle of it it was amazing, helped me retain better and boosted my confidence. I do not think I would have secured a great result if it were not for this course.',
  },
]

export const footerLinks = {
  quickLinks: [
    { label: 'Home', to: '/' },
    { label: 'Courses', to: '/courses/fcps-part-1' },
    { label: 'Question Bank', to: '/courses/fcps-part-1' },
    { label: 'Instructors', to: '/community/mentors' },
    { label: 'About Us', to: '/' },
    { label: 'Contact', to: '/contact' },
  ],
  resources: [
    { label: 'Free Videos', to: '/resources/free-videos' },
    { label: 'Free Notes', to: '/resources/free-notes' },
    { label: 'E-Books', to: '/resources/ebooks' },
    { label: 'Past Papers', to: '/resources/past-papers' },
    { label: 'FCPS Syllabus', to: '/resources/syllabus' },
  ],
  support: [
    { label: 'Help Center', to: '/help-center' },
  ],
}

export const contactInfo = {
  phone: '+92 321 8902931',
  email: 'query@nextgenmedics.info',
  whatsapp: '+92 321 8902931',
  location: 'Pakistan',
  forwardEmail: 'mr.ammadarif@gmail.com',
}

export const faqs = [
  {
    question: 'What is the fees of the course?',
    answer: 'The fees of the course before 28th June is 8000 and after that, it will be 10,000.',
  },
  {
    question: 'What are timings of the classes?',
    answer: 'The classes will be conducted 6 days in a week, each day for 1.5 - 2hrs, the timing will be after maghrib either 7:30 to 9:00 or 9:00 to 10:30.',
  },
  {
    question: 'Which books will be covered?',
    answer: 'The major reference will be from First Aid and SK, but will cover many concepts from different sources.',
  },
  {
    question: 'Will each and every topic be covered in live class?',
    answer: 'The course will be a combination of live lectures, pre-recorded lectures, quiz assignments, notes assignments.',
  },
  {
    question: 'Will you share the schedule before hand?',
    answer: 'Yes, we will share the schedule before hand.',
  },
  {
    question: 'Will you teach for all the fields?',
    answer: 'No, We are covering General Medicine and Surgery Only, for other domains, you can get benefit for Paper I only, for Paper II, you have to study by yourself.',
  },
]
