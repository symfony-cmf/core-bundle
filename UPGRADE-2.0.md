UPGRADE FROM 1.x to 2.0
=======================

### SonataAdmin Support

 * The Admin extension to edit seo metadata was moved into `symfony-cmf/sonata-admin-integration-bundle`.
   With the move, the admin extension service names also changed. If you are using the one of the core extensions,
   you need to adjust your configuration.
   
   Before:
   
   ```yaml
        # app/config/config.yml
     
        sonata_admin:
            extensions:
                cmf_core.admin_extension.child:
                   implements:
                        Symfony\Cmf\Bundle\CoreBundle\Model\ChildInterface
                cmf_core.admin_extension.publish_workflow.time_period:
                      implements:
                          - Symfony\Cmf\Bundle\CoreBundle\PublishWorkflow\PublishTimePeriodInterface
                cmf_core.admin_extension.publish_workflow.publishable:
                      implements:
                          - Symfony\Cmf\Bundle\CoreBundle\PublishWorkflow\PublishableInterface
   ```

    After:
       
   ```yaml
        # app/config/config.yml
                
        sonata_admin:
            extensions:
                 cmf_sonata_admin_integration.core.extension.child:
                    implements:
                        Symfony\Cmf\Bundle\CoreBundle\Model\ChildInterface
                 cmf_sonata_admin_integration.core.extension.publish_workflow.time_period:
                      implements:
                          - Symfony\Cmf\Bundle\CoreBundle\PublishWorkflow\PublishTimePeriodInterface
                 cmf_sonata_admin_integration.core.extension.publish_workflow.publishable:
                       implements:
                           - Symfony\Cmf\Bundle\CoreBundle\PublishWorkflow\PublishableInterface
   ```
