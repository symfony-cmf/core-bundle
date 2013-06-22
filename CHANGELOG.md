Changelog
=========

* **2013-06-20**: [PublishWorkflow] Moved the access checks to security voter
  and using isGranted 'VIEW' instead.
  Removed twig function cmf_is_published, just use is_granted('VIEW', content)
  instead.
  Configuration was adjusted: The parameter for the role that may see unpublished
  content moved from `role` to `publish_workflow.view_non_published_role`. The
  publish_workflow_listener moved to `publish_workflow.request_listener`.

* **2013-05-16**: [PublishWorkFlowChecker] Removed Request argument
  from check method. Class now accepts a DateTime object to
  optionally "set" the current time.
