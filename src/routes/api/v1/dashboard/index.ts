import { docker } from '$lib/api/docker';
import type { Request } from '@sveltejs/kit';
import Configuration from '$models/Configuration'
export async function get(request: Request) {
	// Should update this to get data from mongodb and update db with the currently running services on start!
	const dockerServices = await docker.engine.listServices();
	// let applications: any = dockerServices.filter(
	// 	(r) =>
	// 		r.Spec.Labels.managedBy === 'coolify' &&
	// 		r.Spec.Labels.type === 'application' &&
	// 		r.Spec.Labels.configuration
	// );
	let databases: any = dockerServices.filter(
		(r) =>
			r.Spec.Labels.managedBy === 'coolify' &&
			r.Spec.Labels.type === 'database' &&
			r.Spec.Labels.configuration
	);
	let services: any = dockerServices.filter(
		(r) =>
			r.Spec.Labels.managedBy === 'coolify' &&
			r.Spec.Labels.type === 'service' &&
			r.Spec.Labels.configuration
	);
	// applications = applications.map((r) => {
	// 	const configuration = JSON.parse(r.Spec.Labels.configuration)
	// 	if (configuration) {
	// 		const found = applications.find(a => {
	// 			const conf = JSON.parse(a.Spec.Labels.configuration)
	// 			if (
	// 				conf.repository.id === configuration.repository.id &&
	// 				conf.repository.branch === configuration.repository.branch &&
	// 				conf.repository.pullRequest && conf.repository.pullRequest !== 0
	// 			) {
	// 				return true
	// 			}
	// 		})
	// 		return {
	// 			configuration,
	// 			prBuilds: found ? true : false,
	// 			UpdatedAt: r.UpdatedAt
	// 		};
	// 	}
	// 	return {};
	// });

	databases = databases.map((r) => {
		if (JSON.parse(r.Spec.Labels.configuration)) {
			return {
				configuration: JSON.parse(r.Spec.Labels.configuration)
			};
		}
		return {};
	});
	services = services.map((r) => {
		if (JSON.parse(r.Spec.Labels.configuration)) {
			return {
				serviceName: r.Spec.Labels.serviceName,
				configuration: JSON.parse(r.Spec.Labels.configuration)
			};
		}
		return {};
	});
	// console.log(applications)
	// applications = [
	// 	...new Map(
	// 		applications
	// 			.filter(f => f.configuration.repository.pullRequest === 0 || !f.configuration.repository.pullRequest)
	// 			.map((item) => [
	// 				item.configuration.repository.id + item.configuration.repository.branch,
	// 				item
	// 			])
	// 	).values()
	// ];
	const conf = await Configuration.find().select('-_id -__v -createdAt')
	const b = []
	for (const c of conf) {
		const found = conf.find(a => {

			const conf = a
			if (
				conf.repository.id === c.repository.id &&
				conf.repository.branch === c.repository.branch &&
				conf.repository.pullRequest && conf.repository.pullRequest !== 0
			) {
				return true
			}
		})
		const a = {
			configuration: c,
			UpdatedAt: c.updatedAt,
			prBuilds: found ? true : false,
		}
		b.push(a)
	}
	return {
		status: 200,
		body: {
			success: true,
			applications: {
				deployed: b
			},
			databases: {
				deployed: databases
			},
			services: {
				deployed: services
			}
		}
	};
}
