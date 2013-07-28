Changelog
=========

* **2013-07-26**: The CoreBundle now supports translatable models. For
  phpcr-odm you need to configure the locales or a metadata listener will
  convert the properties to not translated.

* **2013-06-20**: [PublishWorkflow] The PublishWorkflowChecker now implements
  SecurityContextInterface and the individual checks are moved to voters.
  Use the service cmf_core.publish_workflow.checker and call
  `isGranted('VIEW', $content)` - or `'VIEW_ANONYMOUS'` if you don't want to
  see unpublished content even if the current user is allowed to see it.
  Configuration was adjusted: The parameter for the role that may see unpublished
  content moved from `role` to `publish_workflow.view_non_published_role`.
  The security context is also triggered by a core security voter, so that
  using the isGranted method of the standard security will check for
  publication.
  The PublishWorkflowInterface is split into the reading interfaces
  PublishableInterface and PublishTimePeriodInterface as well as
  PublishableWriteInterface and PublishableTimePeriodWriteInterface. The sonata
  admin extension has been split accordingly and there are now
  cmf_core.admin_extension.publish_workflow.time_period and
  cmf_core.admin_extension.publish_workflow.publishable.

* **2013-05-16**: [PublishWorkFlowChecker] Removed Request argument
  from check method. Class now accepts a DateTime object to
  optionally "set" the current time.
