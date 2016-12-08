<?php

return [

	"models" => [

		//Model Image
		"ModelImage" => array(
			'id' => 'model_image_id',
			'name' => 'ModelImage',
			'hrNamePlural' => 'Images',
			'hrName' => 'Image',
			'printProperty' => 'name',
			'faIcon' => 'fa-photo',

			'standalone' => false,
			'requiredParents' => array(

				// THIS FIELD IS REQUIRED

			),

			'formFields' => array(
				array(
					'property' => 'imagename',
					'displayProperty' => array(
						'type' => 'image',
						'display' => 'image',
						'method' => 'image'
					),
					'label' => 'Image',
					'type' => 'image',
					'rules' => 'image',
					'showDimensions' => true,
					'sideTable' => true,
				),
				array(
					'property' => 'caption',
					'label' => 'Caption',
					'type' => 'text'
				),
			),
		),

		//Model File
		"ModelFile" => array(
			'id' => 'model_file_id',
			'name' => 'ModelFile',
			'hrNamePlural' => 'Files',
			'hrName' => 'File',
			'printProperty' => 'title',
			'faIcon' => 'fa-file',

			'standalone' => false,
			'requiredParents' => array(

				// THIS FIELD IS REQUIRED

			),

			'formFields' => array(
				array(
					'property' => 'title',
					'label' => 'Title',
					'type' => 'text',
					'sideTable' => true,
					'sideTableLink' => true,
					'rules' => 'required'
				),
				array(
					'property' => 'filename',
					'displayProperty' => array(
						'type' => 'file',
						'display' => 'url',
						'method' => 'file'
					),
					'label' => 'File',
					'info' => 'Leave empty to keep current file.',
					'type' => 'file',
					'rules' => 'mimes:pdf',
					'sideTable' => true,
					'hiddenInfo' => array(
						'add' => true
					)
				),
			),
		),

		//Page
		"Page" => array(
			'id' => 'page_id',
			'name' => 'Page',
			'hrNamePlural' => 'Pages',
			'hrName' => 'Page',
			'printProperty' => 'name',
			'faIcon' => 'fa-book',

			// tree-structure config
			'index' => 'tree',
			'linkProperty' => 'name',
			'maxDepth' => 2, // 0, 1, 2
			'parent' =>  array(
				'name' => 'Page',
				'method' => 'parentPage',
				'property' => 'page_id',
			),
			'children' => array(
				'name' => 'Page',
				'method' => 'pages'
			),
			'generateSlug' => true,
			'slugProperty' => 'name',
			'skipFirstLevelSlug' => true,
			'maxFirstLevelItems' => 1,
			// end tree-structure config

			'tabs' => array(
				'Common', 'Meta'
			),

			'formFields' => array(
				array(
					'property' => 'model_key',
					'label' => 'Page Key',
					'type' => 'select',
					'rules' => 'required',
					'selectType' => array(
						'type' => 'list',
						'listMethod' => 'getPageKeyList'
					),
					'required' => false,
					'tab' => 'Common'
				),
				array(
					'property' => 'name',
					'label' => 'Name',
					'type' => 'text',
					'rules' => 'required',
					'tab' => 'Common'
				),
				array(
					'property' => 'title',
					'label' => 'Title',
					'type' => 'text',
					'rules' => 'required',
					'tab' => 'Common'
				),
				array(
					'property' => 'content',
					'label' => 'Content',
					'type' => 'textarea',
					'options' => array(
						'class' => 'simpleEditor'
					),
					'tab' => 'Common',
					'modelKey' => array(
						'home'
					)
				),
				array(
					'property' => 'meta_keywords',
					'label' => 'Meta Keywords',
					'type' => 'text',
					'tab' => 'Meta'
				),
				array(
					'property' => 'meta_description',
					'label' => 'Meta Description',
					'type' => 'textarea',
					'options' => array(
						'class' => 'shortTextarea'
					),
					'tab' => 'Meta'
				)
			)
		),

		//User
		"User" => array(
			'id' => 'user_id',
			'name' => 'User',
			'hrNamePlural' => 'Users',
			'hrName' => 'User',
			'printProperty' => 'email',
			'faIcon' => 'fa-users',

			'orderBy' => 'created_at',
			'direction' => 'desc',
			'perPage' => 5,

			'formFields' => array(
				array(
					'property' => 'role',
					'label' => 'User Role',
					'type' => 'select',
					'displayProperty' => array(
						'type' => 'accessor',
						'method' => 'roleName'
					),
					'selectType' => array(
						'type' => 'list',
						'listMethod' => 'getUserRoles'
					),
					'rules' => 'required',
					'required' => true,
					'search' => array(
						'type' => 'standard',
						'match' => 'exact'
					),
					'excelExport' => true
				),
				array(
					'property' => 'email',
					'label' => 'Email',
					'type' => 'text',
					'rules' => 'required|unique:users,email,{ignoreId},id|email',
					'table' => true,
					'order' => true,
					'tableLink' => true,
					'search' => array(
						'type' => 'standard',
						'match' => 'pattern'
					),
					'excelExport' => true
				),
				array(
					'property' => 'password',
					'label' => 'Password',
					'type' => 'text',
					'rules' => '{addRequired}',
					'info' => 'Leave empty to keep current password',
					'hiddenInfo' => array(
						'add' => true
					),
					'options' => array(
						'autocomplete' => 'off'
					),
					'autofill' => false
				),
				array(
					'property' => 'is_superadmin',
					'label' => 'Is SuperAdmin',
					'type' => 'checkbox',
					'restrictedToSuperadmin' => true,
					'search' => array(
						'type' => 'standard',
						'match' => 'exact'
					),
					'table' => true,
				),
				array(
					'property' => 'first_name',
					'label' => 'First Name',
					'type' => 'text',
					'rules' => 'required',
					'table' => true,
					'order' => true,
					'search' => array(
						'type' => 'standard',
						'match' => 'pattern'
					),
					'excelExport' => true
				),
				array(
					'property' => 'last_name',
					'label' => 'Last Name',
					'type' => 'text',
					'rules' => 'required',
					'table' => true,
					'order' => true,
					'search' => array(
						'type' => 'standard',
						'match' => 'pattern'
					),
					'excelExport' => true
				),
				array(
					'property' => 'created_at',
					'displayProperty' => array(
						'type' => 'accessor',
						'method' => 'indexDate'
					),
					'label' => 'Created',
					'type' => 'text',
					'table' => true,
					'order' => true,
					'hidden' => array(
						'add' => true, 'edit' => true
					),
					'excelExport' => true
				)
			)
		),

		//Gtcms Setting
		/*"GtcmsSetting" => array(
			'id' => 'gtcms_setting_id',
			'name' => 'GtcmsSetting',
			'hrName' => 'Settings',
			'hrNamePlural' => 'Settings',
			'faIcon' => 'fa-cog',

			'formFields' => array(

			)
		),*/

	]

];