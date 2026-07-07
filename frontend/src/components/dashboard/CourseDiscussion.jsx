import DiscussionBoard from './DiscussionBoard'

export default function CourseDiscussion({ courseId, canModerate = false }) {
  return <DiscussionBoard courseId={courseId} canModerate={canModerate} />
}
