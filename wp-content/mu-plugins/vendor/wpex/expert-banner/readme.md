# Expert Banner link

WPEX WordPress plugin for Expert Banner. Provides a link to expert services in the header of the editor screen. Intended to be used as a sub-plugin of System Plugin.

![image](https://user-images.githubusercontent.com/84349138/220201263-ba3829b1-1073-4f39-b67c-26ba1bbc66aa.png)

## Deploy latest to the System Plugin

When we are ready for a release we need to tag a new version to release and then update the system plugin reference with the latest tag. Determine the next release tag using [semantic versioning](https://semver.org/) and run the following script.

`composer run prepare-release X.X.X`

Finally, create a PR in the [WPPaaS/wp-paas-system-plugin](https://github.com/gdcorp-wordpress/wp-paas-system-plugin) repository and assign to @mrebernisak. [Example](https://github.com/gdcorp-wordpress/wp-paas-system-plugin/pull/54). The release script will output a link to the branch diff which you can create a PR from.

---
*The `build` directory must be included in version control for the system plugin deployment. This is not ideal but is currently the way it must be until the system plugin deployment process is updated.*