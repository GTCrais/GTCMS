<?php

return [

	"models" => [

		//Model Image
		"ModelImage" => [
			'id' => 'model_image_id',
			'name' => 'ModelImage',
			'hrNamePlural' => 'Images',
			'hrName' => 'Image',
			'printProperty' => 'name',
			'faIcon' => 'fa-photo',

			'standalone' => false,
			'requiredParents' => [

				// THIS FIELD IS REQUIRED

			],

			'formFields' => [
				[
					'property' => 'imagename',
					'displayProperty' => [
						'type' => 'image',
						'display' => 'image',
						'method' => 'image'
					],
					'label' => 'Image',
					'type' => 'image',
					'rules' => 'image',
					'showDimensions' => true,
					'sideTable' => true,
				],
				[
					'property' => 'caption',
					'label' => 'Caption',
					'type' => 'text'
				],
			],
		],

		//Model File
		"ModelFile" => [
			'id' => 'model_file_id',
			'name' => 'ModelFile',
			'hrNamePlural' => 'Files',
			'hrName' => 'File',
			'printProperty' => 'title',
			'faIcon' => 'fa-file',

			'standalone' => false,
			'requiredParents' => [

				// THIS FIELD IS REQUIRED

			],

			'formFields' => [
				[
					'property' => 'title',
					'label' => 'Title',
					'type' => 'text',
					'sideTable' => true,
					'sideTableLink' => true,
					'rules' => 'required'
				],
				[
					'property' => 'filename',
					'label' => 'File',
					'type' => 'file',
					'rules' => 'mimes:pdf',
					'sideTable' => true
				],
			],
		],

		//Page
		"Page" => [
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
			'parent' => [
				'name' => 'Page',
				'method' => 'parentPage',
				'property' => 'page_id',
			],
			'children' => [
				'name' => 'Page',
				'method' => 'pages'
			],
			'generateSlug' => true,
			'slugProperty' => 'name',
			'skipFirstLevelSlug' => true,
			'maxFirstLevelItems' => 1,
			// end tree-structure config

			'tabs' => [
				'Common', 'Meta'
			],

			'formFields' => [
				[
					'property' => 'model_key',
					'label' => 'Page Key',
					'type' => 'select',
					'rules' => 'required',
					'selectType' => [
						'type' => 'list',
						'listMethod' => 'getPageKeyList'
					],
					'tab' => 'Common'
				],
				[
					'property' => 'name',
					'label' => 'Name',
					'type' => 'text',
					'rules' => 'required',
					'tab' => 'Common'
				],
				[
					'property' => 'title',
					'label' => 'Title',
					'type' => 'text',
					'rules' => 'required',
					'tab' => 'Common'
				],
				[
					'property' => 'content',
					'label' => 'Content',
					'type' => 'textarea',
					'options' => [
						'class' => 'editor',
						'data-editortoolbar' => 'bold-italic|bullet-list|justify|link'
					],
					'tab' => 'Common',
					'modelKey' => [
						'home'
					]
				],
				[
					'property' => 'meta_keywords',
					'label' => 'Meta Keywords',
					'type' => 'textarea',
					'options' => [
						'class' => 'autosize'
					],
					'tab' => 'Meta'
				],
				[
					'property' => 'meta_description',
					'label' => 'Meta Description',
					'type' => 'textarea',
					'options' => [
						'class' => 'autosize'
					],
					'tab' => 'Meta'
				]
			]
		],

		//User
		"User" => [
			'id' => 'user_id',
			'name' => 'User',
			'hrNamePlural' => 'Users',
			'hrName' => 'User',
			'printProperty' => 'email',
			'faIcon' => 'fa-users',

			'orderBy' => 'created_at',
			'direction' => 'desc',
			'perPage' => 5,

			'formFields' => [
				[
					'property' => 'role',
					'label' => 'User Role',
					'type' => 'select',
					'displayProperty' => [
						'type' => 'accessor',
						'method' => 'roleName'
					],
					'selectType' => [
						'type' => 'list',
						'listMethod' => 'getUserRoles'
					],
					'rules' => 'required',
					'default' => 'user',
					'search' => [
						'type' => 'standard',
						'match' => 'exact'
					],
					'excelExport' => true
				],
				[
					'property' => 'email',
					'label' => 'Email',
					'type' => 'text',
					'rules' => 'required|unique:users,email,{ignoreId},id|email',
					'table' => true,
					'order' => true,
					'tableLink' => true,
					'search' => [
						'type' => 'standard',
						'match' => 'pattern'
					],
					'excelExport' => true
				],
				[
					'property' => 'password',
					'label' => 'Password',
					'type' => 'text',
					'rules' => '{addRequired}',
					'info' => 'Leave empty to keep current password',
					'hiddenInfo' => [
						'add' => true
					],
					'options' => [
						'autocomplete' => 'off'
					],
					'autofill' => false
				],
				[
					'property' => 'is_superadmin',
					'label' => 'Is SuperAdmin',
					'type' => 'checkbox',
					'restrictedToSuperadmin' => true,
					'search' => [
						'type' => 'standard',
						'match' => 'exact'
					],
					'table' => true,
				],
				[
					'property' => 'first_name',
					'label' => 'First Name',
					'type' => 'text',
					'rules' => 'required',
					'table' => true,
					'order' => true,
					'search' => [
						'type' => 'standard',
						'match' => 'pattern'
					],
					'excelExport' => true
				],
				[
					'property' => 'last_name',
					'label' => 'Last Name',
					'type' => 'text',
					'rules' => 'required',
					'table' => true,
					'order' => true,
					'search' => [
						'type' => 'standard',
						'match' => 'pattern'
					],
					'excelExport' => true
				],
				[
					'property' => 'created_at',
					'displayProperty' => [
						'type' => 'accessor',
						'method' => 'indexDate'
					],
					'label' => 'Created',
					'type' => 'text',
					'table' => true,
					'order' => true,
					'hidden' => [
						'add' => true, 'edit' => true
					],
					'excelExport' => true
				]
			]
		],

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