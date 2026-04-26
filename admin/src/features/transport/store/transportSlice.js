import { createAsyncThunk, createSlice } from '@reduxjs/toolkit';
import { transportApi } from '../services/transportApi';

function getMessage(error, fallback) {
  return error.response?.data?.message || fallback;
}

function normalizePaginated(payload) {
  return {
    items: payload?.data || [],
    pagination: {
      page: payload?.current_page || 1,
      totalPages: payload?.last_page || 1,
      total: payload?.total || 0,
    },
  };
}

function upsertItem(items, payload) {
  const exists = items.some((item) => item.id === payload.id);
  if (!exists) {
    return [payload, ...items];
  }

  return items.map((item) => (item.id === payload.id ? payload : item));
}

function createMutationThunk(type, request, fallback) {
  return createAsyncThunk(type, async (payload, thunkApi) => {
    try {
      const response = await request(payload);
      return response.data.data;
    } catch (error) {
      return thunkApi.rejectWithValue(getMessage(error, fallback));
    }
  });
}

function createUpdateThunk(type, request, fallback) {
  return createAsyncThunk(type, async ({ id, payload }, thunkApi) => {
    try {
      const response = await request(id, payload);
      return response.data.data;
    } catch (error) {
      return thunkApi.rejectWithValue(getMessage(error, fallback));
    }
  });
}

function createDeleteThunk(type, request, fallback) {
  return createAsyncThunk(type, async (id, thunkApi) => {
    try {
      await request(id);
      return id;
    } catch (error) {
      return thunkApi.rejectWithValue(getMessage(error, fallback));
    }
  });
}

export const fetchTransportReferenceData = createAsyncThunk('transport/fetchTransportReferenceData', async (_, thunkApi) => {
  try {
    const [
      vehicles,
      drivers,
      routes,
      routeStops,
      routeAssignments,
      academicYears,
      students,
      staffMembers,
    ] = await Promise.all([
      transportApi.getVehicles({ per_page: 100 }),
      transportApi.getDrivers({ per_page: 100 }),
      transportApi.getRoutes({ per_page: 100 }),
      transportApi.getRouteStops({ per_page: 100 }),
      transportApi.getRouteAssignments({ per_page: 100 }),
      transportApi.getAcademicYears({ per_page: 100 }),
      transportApi.getStudents({ per_page: 100 }),
      transportApi.getStaff({ per_page: 100 }),
    ]);

    return {
      vehicles: vehicles.data.data?.data || [],
      drivers: drivers.data.data?.data || [],
      routes: routes.data.data?.data || [],
      routeStops: routeStops.data.data?.data || [],
      routeAssignments: routeAssignments.data.data?.data || [],
      academicYears: academicYears.data.data || [],
      students: students.data.data || [],
      staffMembers: staffMembers.data.data || [],
    };
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load transport reference data.'));
  }
});

export const fetchVehicles = createAsyncThunk('transport/fetchVehicles', async (params = {}, thunkApi) => {
  try {
    const response = await transportApi.getVehicles(params);
    return normalizePaginated(response.data.data);
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load vehicles.'));
  }
});

export const createVehicle = createMutationThunk('transport/createVehicle', transportApi.createVehicle, 'Failed to create vehicle.');
export const updateVehicle = createUpdateThunk('transport/updateVehicle', transportApi.updateVehicle, 'Failed to update vehicle.');
export const deleteVehicle = createDeleteThunk('transport/deleteVehicle', transportApi.deleteVehicle, 'Failed to delete vehicle.');

export const fetchDrivers = createAsyncThunk('transport/fetchDrivers', async (params = {}, thunkApi) => {
  try {
    const response = await transportApi.getDrivers(params);
    return normalizePaginated(response.data.data);
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load drivers.'));
  }
});

export const createDriver = createMutationThunk('transport/createDriver', transportApi.createDriver, 'Failed to create driver.');
export const updateDriver = createUpdateThunk('transport/updateDriver', transportApi.updateDriver, 'Failed to update driver.');
export const deleteDriver = createDeleteThunk('transport/deleteDriver', transportApi.deleteDriver, 'Failed to delete driver.');

export const fetchRoutes = createAsyncThunk('transport/fetchRoutes', async (params = {}, thunkApi) => {
  try {
    const response = await transportApi.getRoutes(params);
    return normalizePaginated(response.data.data);
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load routes.'));
  }
});

export const createRoute = createMutationThunk('transport/createRoute', transportApi.createRoute, 'Failed to create route.');
export const updateRoute = createUpdateThunk('transport/updateRoute', transportApi.updateRoute, 'Failed to update route.');
export const deleteRoute = createDeleteThunk('transport/deleteRoute', transportApi.deleteRoute, 'Failed to delete route.');

export const fetchRouteStops = createAsyncThunk('transport/fetchRouteStops', async (params = {}, thunkApi) => {
  try {
    const response = await transportApi.getRouteStops(params);
    return normalizePaginated(response.data.data);
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load route stops.'));
  }
});

export const createRouteStop = createMutationThunk('transport/createRouteStop', transportApi.createRouteStop, 'Failed to create route stop.');
export const updateRouteStop = createUpdateThunk('transport/updateRouteStop', transportApi.updateRouteStop, 'Failed to update route stop.');
export const deleteRouteStop = createDeleteThunk('transport/deleteRouteStop', transportApi.deleteRouteStop, 'Failed to delete route stop.');

export const fetchRouteAssignments = createAsyncThunk('transport/fetchRouteAssignments', async (params = {}, thunkApi) => {
  try {
    const response = await transportApi.getRouteAssignments(params);
    return normalizePaginated(response.data.data);
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load route assignments.'));
  }
});

export const createRouteAssignment = createMutationThunk('transport/createRouteAssignment', transportApi.createRouteAssignment, 'Failed to create route assignment.');
export const updateRouteAssignment = createUpdateThunk('transport/updateRouteAssignment', transportApi.updateRouteAssignment, 'Failed to update route assignment.');
export const deleteRouteAssignment = createDeleteThunk('transport/deleteRouteAssignment', transportApi.deleteRouteAssignment, 'Failed to delete route assignment.');

export const fetchStudentAllocations = createAsyncThunk('transport/fetchStudentAllocations', async (params = {}, thunkApi) => {
  try {
    const response = await transportApi.getStudentAllocations(params);
    return normalizePaginated(response.data.data);
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load student transport allocations.'));
  }
});

export const createStudentAllocation = createMutationThunk('transport/createStudentAllocation', transportApi.createStudentAllocation, 'Failed to create student allocation.');
export const updateStudentAllocation = createUpdateThunk('transport/updateStudentAllocation', transportApi.updateStudentAllocation, 'Failed to update student allocation.');
export const deleteStudentAllocation = createDeleteThunk('transport/deleteStudentAllocation', transportApi.deleteStudentAllocation, 'Failed to delete student allocation.');

export const fetchStaffAllocations = createAsyncThunk('transport/fetchStaffAllocations', async (params = {}, thunkApi) => {
  try {
    const response = await transportApi.getStaffAllocations(params);
    return normalizePaginated(response.data.data);
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load staff transport allocations.'));
  }
});

export const createStaffAllocation = createMutationThunk('transport/createStaffAllocation', transportApi.createStaffAllocation, 'Failed to create staff allocation.');
export const updateStaffAllocation = createUpdateThunk('transport/updateStaffAllocation', transportApi.updateStaffAllocation, 'Failed to update staff allocation.');
export const deleteStaffAllocation = createDeleteThunk('transport/deleteStaffAllocation', transportApi.deleteStaffAllocation, 'Failed to delete staff allocation.');

export const fetchTrips = createAsyncThunk('transport/fetchTrips', async (params = {}, thunkApi) => {
  try {
    const response = await transportApi.getTrips(params);
    return normalizePaginated(response.data.data);
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load trips.'));
  }
});

export const createTrip = createMutationThunk('transport/createTrip', transportApi.createTrip, 'Failed to create trip.');
export const updateTrip = createUpdateThunk('transport/updateTrip', transportApi.updateTrip, 'Failed to update trip.');
export const deleteTrip = createDeleteThunk('transport/deleteTrip', transportApi.deleteTrip, 'Failed to delete trip.');
export const startTrip = createAsyncThunk('transport/startTrip', async ({ id, payload }, thunkApi) => {
  try {
    const response = await transportApi.startTrip(id, payload);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to start trip.'));
  }
});
export const completeTrip = createAsyncThunk('transport/completeTrip', async ({ id, payload }, thunkApi) => {
  try {
    const response = await transportApi.completeTrip(id, payload);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to complete trip.'));
  }
});
export const cancelTrip = createAsyncThunk('transport/cancelTrip', async ({ id, payload }, thunkApi) => {
  try {
    const response = await transportApi.cancelTrip(id, payload);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to cancel trip.'));
  }
});
export const markBoarded = createMutationThunk('transport/markBoarded', ({ id, payload }) => transportApi.markBoarded(id, payload), 'Failed to mark boarded.');
export const markDropped = createMutationThunk('transport/markDropped', ({ id, payload }) => transportApi.markDropped(id, payload), 'Failed to mark dropped.');

export const fetchTripLogs = createAsyncThunk('transport/fetchTripLogs', async (params = {}, thunkApi) => {
  try {
    const response = await transportApi.getTripLogs(params);
    return normalizePaginated(response.data.data);
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load trip logs.'));
  }
});

export const fetchMaintenanceLogs = createAsyncThunk('transport/fetchMaintenanceLogs', async (params = {}, thunkApi) => {
  try {
    const response = await transportApi.getMaintenanceLogs(params);
    return normalizePaginated(response.data.data);
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load maintenance logs.'));
  }
});
export const createMaintenanceLog = createMutationThunk('transport/createMaintenanceLog', transportApi.createMaintenanceLog, 'Failed to create maintenance log.');
export const updateMaintenanceLog = createUpdateThunk('transport/updateMaintenanceLog', transportApi.updateMaintenanceLog, 'Failed to update maintenance log.');
export const deleteMaintenanceLog = createDeleteThunk('transport/deleteMaintenanceLog', transportApi.deleteMaintenanceLog, 'Failed to delete maintenance log.');

export const fetchFuelLogs = createAsyncThunk('transport/fetchFuelLogs', async (params = {}, thunkApi) => {
  try {
    const response = await transportApi.getFuelLogs(params);
    return normalizePaginated(response.data.data);
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load fuel logs.'));
  }
});
export const createFuelLog = createMutationThunk('transport/createFuelLog', transportApi.createFuelLog, 'Failed to create fuel log.');
export const updateFuelLog = createUpdateThunk('transport/updateFuelLog', transportApi.updateFuelLog, 'Failed to update fuel log.');
export const deleteFuelLog = createDeleteThunk('transport/deleteFuelLog', transportApi.deleteFuelLog, 'Failed to delete fuel log.');

export const fetchGpsLogs = createAsyncThunk('transport/fetchGpsLogs', async (params = {}, thunkApi) => {
  try {
    const response = await transportApi.getGpsLogs(params);
    return normalizePaginated(response.data.data);
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load GPS logs.'));
  }
});
export const createGpsLog = createMutationThunk('transport/createGpsLog', transportApi.createGpsLog, 'Failed to create GPS log.');

export const fetchVehicleLocation = createAsyncThunk('transport/fetchVehicleLocation', async (vehicleId, thunkApi) => {
  try {
    const response = await transportApi.getVehicleLocation(vehicleId);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load vehicle location.'));
  }
});

export const fetchTransportReports = createAsyncThunk('transport/fetchTransportReports', async (params = {}, thunkApi) => {
  try {
    const response = await transportApi.getReports(params);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load transport reports.'));
  }
});

const initialState = {
  vehicles: [],
  drivers: [],
  routes: [],
  routeStops: [],
  routeAssignments: [],
  studentAllocations: [],
  staffAllocations: [],
  trips: [],
  tripLogs: [],
  maintenanceLogs: [],
  fuelLogs: [],
  gpsLogs: [],
  reports: null,
  vehicleLocation: null,
  academicYears: [],
  students: [],
  staffMembers: [],
  loading: false,
  saving: false,
  error: null,
  vehiclesPagination: { page: 1, totalPages: 1, total: 0 },
  driversPagination: { page: 1, totalPages: 1, total: 0 },
  routesPagination: { page: 1, totalPages: 1, total: 0 },
  routeStopsPagination: { page: 1, totalPages: 1, total: 0 },
  routeAssignmentsPagination: { page: 1, totalPages: 1, total: 0 },
  studentAllocationsPagination: { page: 1, totalPages: 1, total: 0 },
  staffAllocationsPagination: { page: 1, totalPages: 1, total: 0 },
  tripsPagination: { page: 1, totalPages: 1, total: 0 },
  tripLogsPagination: { page: 1, totalPages: 1, total: 0 },
  maintenanceLogsPagination: { page: 1, totalPages: 1, total: 0 },
  fuelLogsPagination: { page: 1, totalPages: 1, total: 0 },
  gpsLogsPagination: { page: 1, totalPages: 1, total: 0 },
};

const transportSlice = createSlice({
  name: 'transport',
  initialState,
  reducers: {},
  extraReducers: (builder) => {
    builder
      .addCase(fetchTransportReferenceData.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(fetchTransportReferenceData.fulfilled, (state, action) => {
        state.loading = false;
        state.vehicles = action.payload.vehicles;
        state.drivers = action.payload.drivers;
        state.routes = action.payload.routes;
        state.routeStops = action.payload.routeStops;
        state.routeAssignments = action.payload.routeAssignments;
        state.academicYears = action.payload.academicYears;
        state.students = action.payload.students;
        state.staffMembers = action.payload.staffMembers;
      })
      .addCase(fetchTransportReferenceData.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload;
      })
      .addCase(fetchVehicleLocation.fulfilled, (state, action) => {
        state.vehicleLocation = action.payload;
      })
      .addCase(fetchTransportReports.fulfilled, (state, action) => {
        state.loading = false;
        state.reports = action.payload;
      })
      .addCase(fetchTransportReports.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(fetchTransportReports.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload;
      });

    [
      [fetchVehicles, 'vehicles', 'vehiclesPagination'],
      [fetchDrivers, 'drivers', 'driversPagination'],
      [fetchRoutes, 'routes', 'routesPagination'],
      [fetchRouteStops, 'routeStops', 'routeStopsPagination'],
      [fetchRouteAssignments, 'routeAssignments', 'routeAssignmentsPagination'],
      [fetchStudentAllocations, 'studentAllocations', 'studentAllocationsPagination'],
      [fetchStaffAllocations, 'staffAllocations', 'staffAllocationsPagination'],
      [fetchTrips, 'trips', 'tripsPagination'],
      [fetchTripLogs, 'tripLogs', 'tripLogsPagination'],
      [fetchMaintenanceLogs, 'maintenanceLogs', 'maintenanceLogsPagination'],
      [fetchFuelLogs, 'fuelLogs', 'fuelLogsPagination'],
      [fetchGpsLogs, 'gpsLogs', 'gpsLogsPagination'],
    ].forEach(([thunk, key, paginationKey]) => {
      builder
        .addCase(thunk.pending, (state) => {
          state.loading = true;
          state.error = null;
        })
        .addCase(thunk.fulfilled, (state, action) => {
          state.loading = false;
          state[key] = action.payload.items;
          state[paginationKey] = action.payload.pagination;
        })
        .addCase(thunk.rejected, (state, action) => {
          state.loading = false;
          state.error = action.payload;
        });
    });

    [
      [createVehicle, 'vehicles'],
      [updateVehicle, 'vehicles'],
      [createDriver, 'drivers'],
      [updateDriver, 'drivers'],
      [createRoute, 'routes'],
      [updateRoute, 'routes'],
      [createRouteStop, 'routeStops'],
      [updateRouteStop, 'routeStops'],
      [createRouteAssignment, 'routeAssignments'],
      [updateRouteAssignment, 'routeAssignments'],
      [createStudentAllocation, 'studentAllocations'],
      [updateStudentAllocation, 'studentAllocations'],
      [createStaffAllocation, 'staffAllocations'],
      [updateStaffAllocation, 'staffAllocations'],
      [createTrip, 'trips'],
      [updateTrip, 'trips'],
      [startTrip, 'trips'],
      [completeTrip, 'trips'],
      [cancelTrip, 'trips'],
      [createMaintenanceLog, 'maintenanceLogs'],
      [updateMaintenanceLog, 'maintenanceLogs'],
      [createFuelLog, 'fuelLogs'],
      [updateFuelLog, 'fuelLogs'],
      [createGpsLog, 'gpsLogs'],
    ].forEach(([thunk, key]) => {
      builder
        .addCase(thunk.pending, (state) => {
          state.saving = true;
          state.error = null;
        })
        .addCase(thunk.fulfilled, (state, action) => {
          state.saving = false;
          state[key] = upsertItem(state[key], action.payload);
        })
        .addCase(thunk.rejected, (state, action) => {
          state.saving = false;
          state.error = action.payload;
        });
    });

    [deleteVehicle, deleteDriver, deleteRoute, deleteRouteStop, deleteRouteAssignment, deleteStudentAllocation, deleteStaffAllocation, deleteTrip, deleteMaintenanceLog, deleteFuelLog].forEach((thunk) => {
      builder
        .addCase(thunk.pending, (state) => {
          state.saving = true;
          state.error = null;
        })
        .addCase(thunk.rejected, (state, action) => {
          state.saving = false;
          state.error = action.payload;
        });
    });

    builder
      .addCase(deleteVehicle.fulfilled, (state, action) => {
        state.saving = false;
        state.vehicles = state.vehicles.filter((item) => item.id !== action.payload);
      })
      .addCase(deleteDriver.fulfilled, (state, action) => {
        state.saving = false;
        state.drivers = state.drivers.filter((item) => item.id !== action.payload);
      })
      .addCase(deleteRoute.fulfilled, (state, action) => {
        state.saving = false;
        state.routes = state.routes.filter((item) => item.id !== action.payload);
      })
      .addCase(deleteRouteStop.fulfilled, (state, action) => {
        state.saving = false;
        state.routeStops = state.routeStops.filter((item) => item.id !== action.payload);
      })
      .addCase(deleteRouteAssignment.fulfilled, (state, action) => {
        state.saving = false;
        state.routeAssignments = state.routeAssignments.filter((item) => item.id !== action.payload);
      })
      .addCase(deleteStudentAllocation.fulfilled, (state, action) => {
        state.saving = false;
        state.studentAllocations = state.studentAllocations.filter((item) => item.id !== action.payload);
      })
      .addCase(deleteStaffAllocation.fulfilled, (state, action) => {
        state.saving = false;
        state.staffAllocations = state.staffAllocations.filter((item) => item.id !== action.payload);
      })
      .addCase(deleteTrip.fulfilled, (state, action) => {
        state.saving = false;
        state.trips = state.trips.filter((item) => item.id !== action.payload);
      })
      .addCase(deleteMaintenanceLog.fulfilled, (state, action) => {
        state.saving = false;
        state.maintenanceLogs = state.maintenanceLogs.filter((item) => item.id !== action.payload);
      })
      .addCase(deleteFuelLog.fulfilled, (state, action) => {
        state.saving = false;
        state.fuelLogs = state.fuelLogs.filter((item) => item.id !== action.payload);
      })
      .addCase(markBoarded.fulfilled, (state, action) => {
        state.saving = false;
        state.tripLogs = upsertItem(state.tripLogs, action.payload);
      })
      .addCase(markDropped.fulfilled, (state, action) => {
        state.saving = false;
        state.tripLogs = upsertItem(state.tripLogs, action.payload);
      });
  },
});

export default transportSlice.reducer;
