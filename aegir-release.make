core = 7.x
api = 2

; This makefile fetches the latest release of Drupal from Drupal.org.
projects[drupal][type] = "core"

; Pin a core version, only as long as we have a core patch below.
; Sync manually with drupal-org-core.make in the hostmaster repository.
;projects[drupal][version] = 7.61

; The release.sh script updates the version of hostmaster.
projects[hostmaster][type] = "profile"
projects[hostmaster][download][type] = "git"
projects[hostmaster][download][tag] = "7.x-3.175"
projects[hostmaster][download][url] = "http://git.drupal.org/project/hostmaster.git"
