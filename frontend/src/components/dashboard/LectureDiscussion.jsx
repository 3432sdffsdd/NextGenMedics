import DiscussionBoard from './DiscussionBoard'

export default function LectureDiscussion({ lectureId, courseId, canModerate = false }) {
  return <DiscussionBoard courseId={courseId} lectureId={lectureId} canModerate={canModerate} />
}
