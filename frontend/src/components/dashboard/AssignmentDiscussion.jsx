import DiscussionBoard from './DiscussionBoard'

/** Discussion board scoped to one assignment (teachers + students). */
export default function AssignmentDiscussion({ courseId, assignmentId, canModerate = false }) {
  return (
    <DiscussionBoard
      courseId={courseId}
      assignmentId={assignmentId}
      canModerate={canModerate}
    />
  )
}
