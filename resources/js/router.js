import VueRouter from 'vue-router'
import Layout from './views/Layout.vue'
import AuthLayout from './views/AuthLayout.vue'

const form_ingebreke = () => import(/* webpackChunkName:"form_ingebreke" */'./views/form_ingebreke.vue').then(m => m.default || m)
const form_bezwaar = () => import(/* webpackChunkName:"form_bezwaar" */'./views/form_bezwaar.vue').then(m => m.default || m)
const form_aanvraag = () => import(/* webpackChunkName:"form_aanvraag" */'./views/form_aanvraag.vue').then(m => m.default || m)
const client = () => import(/* webpackChunkName:"client" */'./views/client.vue').then(m => m.default || m)
const sign = () => import(/* webpackChunkName:"sign" */'./views/sign.vue').then(m => m.default || m)
const thanks = () => import(/* webpackChunkName:"thanks" */'./views/thanks.vue').then(m => m.default || m)
const userpanel = () => import(/* webpackChunkName:"userpanel" */'./views/userpanel.vue').then(m => m.default || m)
const where = () => import(/* webpackChunkName:"where" */'./views/towhere.vue').then(m => m.default || m)
const forgotpwd = () => import(/* webpackChunkName:"forgotpwd" */'./views/forgotpwd.vue').then(m => m.default || m)
const resetpwd = () => import(/* webpackChunkName:"resetpwd" */'./views/resetpwd.vue').then(m => m.default || m)

const routes = [
	{
		path: '/',
		component: Layout,
		children :[
			{
				name: 'noroot',
				path: '/',
				component: form_ingebreke,
			},
			{
				name: 'form_ingebreke',
				path: '/in_gebreke_stellen/',
				component: form_ingebreke,
			},
			{
				name: 'prefilled1',
				path: '/in_gebreke_stellen/:token',
				component: form_ingebreke,
			},
			{
				name: 'form_bezwaar',
				path: '/bezwaar_aantekenen/',
				component: form_bezwaar,
			},
			{
				name: 'prefilled2',
				path: '/bezwaar_aantekenen/:token',
				component: form_bezwaar,
			},
			{
				name: 'form_aanvraag',
				path: '/een_aanvraag_indienen/',
				component: form_aanvraag,
			},
			{
				name: 'prefilled3',
				path: '/een_aanvraag_indienen/:token',
				component: form_aanvraag,
			},
			{
				name: 'client',
				path: '/client/',
				component: client,
			},
			{
				name: 'sign',
				path: '/sign/',
				component: sign,
			},
		]
	},
	{
		name: 'thanks',
		path: '/thanks/',
		component: thanks,
	},
	{
		name: 'userpanel',
		path: '/userpanel/',
		component: userpanel,
	},
	{
		name: 'sessionout',
		path: '/userpanel/:token',
		component: userpanel,
	},
	{
		name: 'where',
		path: '/towhere/:where',
		component: where,
	}, 
	{
		path: '/auth/',
		component: AuthLayout,
		children: [
			{
				name: 'auth.email',
				path: '/auth/password/email/',
				component: forgotpwd,
			},
			{
				path: '/auth/password/reset/:token',
				component: resetpwd,
			},
		]
	},
	
]

const router = new VueRouter({
	mode: 'history',
	routes
});

router.beforeEach((to, from, next) => {
	return next()
});

export default router;
